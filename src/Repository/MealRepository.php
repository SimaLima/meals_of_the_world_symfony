<?php

namespace App\Repository;

use App\Entity\Meal;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr\Join;
use Symfony\Component\HttpFoundation\Request;
use Gedmo\Translatable\TranslatableListener;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @method Meal|null find($id, $lockMode = null, $lockVersion = null)
 * @method Meal|null findOneBy(array $criteria, array $orderBy = null)
 * @method Meal[]    findAll()
 * @method Meal[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MealRepository extends ServiceEntityRepository
{
    private $paginator;

    public function __construct(ManagerRegistry $registry, PaginatorInterface $paginator)
    {
        parent::__construct($registry, Meal::class);
        $this->paginator = $paginator;
    }

    public function filter($data, $request)
    {
        // dump($data);
        $per_page =   (isset($data['per_page'])) ? $data['per_page'] : 5;
        $page_num =   (isset($data['page'])) ? $data['page'] : 1;
        $category =   (isset($data['category'])) ? $data['category'] : null;
        $with =       (isset($data['with'])) ? $data['with'] : [];
        $tags =       (isset($data['tags'])) ? $data['tags'] : [];
        $diff_time =  (isset($data['diff_time'])) ? $data['diff_time'] : null;
        $language =   (isset($data['lang'])) ? $data['lang']->getLocale() : 'en_US'; // required, but still...

        // build query
        $query = $this->createQueryBuilder('meal')
                      ->orderBy('meal.id', 'asc');
        $query = $this->filterByCategory($query, $category);
        $query = $this->filterByTags($query, $tags);
        $query = $this->filterByDiffTime($query, $diff_time);
        $query = $this->addProperties($query, $with);
        $query = $query->getQuery();
        $query->setHydrationMode(Query::HYDRATE_ARRAY);

        // set language of query results
        $query->setHint(
                    Query::HINT_CUSTOM_OUTPUT_WALKER,
                    'Gedmo\\Translatable\\Query\\TreeWalker\\TranslationWalker'
                )
              ->setHint(TranslatableListener::HINT_TRANSLATABLE_LOCALE, $language);

        // get results and paginate data (meta & links included)
        $response = $this->paginateResults(
            $query,
            $page_num,
            $per_page,
            $request->getUri() // current url (for links)
        );

        // calculate 'meal status' and remove unnecessary values (slug, deletedAt...)
        $response['data'] = $this->formatData($response['data'], $diff_time);

        // and finally get back to controller...
        return $response;
    }

    /**
     * FILTER BY CATEGORY
     */
    private function filterByCategory($query, $category)
    {
        if (!$category) return $query;
        if (is_string($category)) $category = strtolower($category);

        if (is_numeric($category)) {
            $query->andWhere('meal.category = :category')
                  ->setParameter('category', (int)$category);
        } elseif ($category == 'null') {
            $query->andWhere('meal.category is NULL');
        } elseif ($category == 'not_null') {
            $query->andWhere('meal.category is not NULL');
        }

        return $query;
    }

    /**
     * FILTER BY TAGS
     */
    private function filterByTags($query, $tags)
    {
        if (empty($tags) || count($tags) == 0) return $query;

        for ($i = 0; $i < count($tags); $i++) {
            $query->innerJoin('meal.tags', 'tags'.$i, Join::WITH, 'tags'.$i.' = :tag'.$i);
            $query->setParameter('tag' . $i, $tags[$i]);
        }

        // goes wild with translations...
        // $query->innerJoin('meal.tags', 'tags')
        //       ->andWhere('tags.id in (:tags)')
        //       ->setParameter('tags', $tags)
        //       ->groupBy('meal.id')
        //       ->having('count(distinct tags.id) = :num_of_tags')
        //       ->setParameter('num_of_tags', count($tags));
        return $query;
    }

    /**
     * FILTER BY DIFF_TIME
     */
    private function filterByDiffTime($query, $diff_time)
    {
        if (!$diff_time) {
            $this->getEntityManager()->getFilters()->enable('softdeleteable');
            return $query;
        }

        $this->getEntityManager()->getFilters()->disable('softdeleteable');
        $query->andWhere('meal.createdAt > :diff_time')
              ->setParameter('diff_time', $diff_time->format('Y-m-d H:i:s'));

        return $query;
    }

    /**
     * ADD PROPERTIES: tags, category, ingredients
     */
    private function addProperties($query, $with)
    {
        if (empty($with) || count($with) == 0) return $query;

        // category
        if (in_array(2, $with)) {
            $query->leftJoin('meal.category', 'meal_category')
                  ->addSelect('meal_category');
        }

        // tags
        if (in_array(1, $with)) {
            $query->leftJoin('meal.tags', 'meal_tags')
                  ->addSelect('meal_tags');
        }

        // ingredients
        if ( in_array(3, $with) ) {
            $query->leftJoin('meal.ingredients', 'meal_ingredients')
                  ->addSelect('meal_ingredients');
        }

        return $query;
    }

    /**
     * Paginate results
     */
    private function paginateResults($query, $page_num, $per_page, $uri)
    {
        // dump($query->getSQL());

        $pagination = $this->paginator->paginate(
            $query,
            $page_num,
            $per_page,
            array('wrap-queries' => true)
        );

        // META
        $total_pages = $pagination->getPageCount();
        $total_items = $pagination->getTotalItemCount();

        $meta = [
            'currentPage' => $page_num,
            'totalItems' => $total_items,
            'itemsPerPage' => $per_page,
            'totalPages' => $total_pages,
        ];

        // LINKS
        $first = null;
        $last = null;
        $prev = null;
        $next = null;
        $self = $uri;

        if ($total_pages > 1) {
            $first = Request::create($self, 'GET', array('filter_meals[page]' => 1))->getUri();
            $last = Request::create($self, 'GET', array('filter_meals[page]' => $total_pages))->getUri();

            // first
            if ($page_num == 1) {
                $next = Request::create($self, 'GET', array('filter_meals[page]' => $page_num+1))->getUri();
            }
            // last
            if ($page_num == $total_pages) {
                $prev = Request::create($self, 'GET', array('filter_meals[page]' => $page_num-1))->getUri();
            }
            // n > 2,3,4 < n
            if ($page_num > 1 && $page_num < $total_pages) {
                $prev = Request::create($self, 'GET', array('filter_meals[page]' => $page_num-1))->getUri();
                $next = Request::create($self, 'GET', array('filter_meals[page]' => $page_num+1))->getUri();
            }
        }

        $links = [
            'first' => $first,
            'last' => $last,
            'prev' => $prev,
            'next' => $next,
            'self' => $self,
        ];

        return [
            'meta' => $meta,
            'data' => $pagination->getItems(),
            'links' => $links,
        ];
    }

    /**
     * Format Data (serializer not working the way I need?)
     */
    private function formatData($results, $diff_time)
    {
        foreach ($results as $key => $result) {
            $status = 'created';

            if ($diff_time) {
                if ($result['deletedAt'] != null) {
                    $status = 'deleted';
                } elseif ($result['createdAt'] != $result['updatedAt']) {
                    $status = 'modified';
                }
            }

            // remove unnecessary
            unset($result['slug']);
            unset($result['updatedAt']);
            unset($result['deletedAt']);

            // rename 'createdAt' to 'status'
            $keys = array_keys($result);
            $keys[array_search('createdAt', $keys)] = 'status';

            // combine everything
            $result = array_combine($keys, $result);

            // set status value
            $result['status'] = $status;

            // override current data with new
            $results[$key] = $result;
        }

        return $results;
    }



    // /**
    //  * @return Meal[] Returns an array of Meal objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Meal
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
