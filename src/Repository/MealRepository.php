<?php

namespace App\Repository;

use App\Entity\Meal;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Common\Collections\Criteria;

use Doctrine\ORM\QueryBuilder as DoctrineQueryBuilder;
use Doctrine\ORM\Tools\Pagination\CountWalker;
use Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator;


/**
 * @method Meal|null find($id, $lockMode = null, $lockVersion = null)
 * @method Meal|null findOneBy(array $criteria, array $orderBy = null)
 * @method Meal[]    findAll()
 * @method Meal[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MealRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Meal::class);
    }


    public function filter($data)
    {
        // dump($data);
        $per_page =   (isset($data['per_page'])) ? $data['per_page'] : 5;
        $page =       (isset($data['page'])) ? $data['page'] : null;
        $category =   (isset($data['category'])) ? $data['category'] : null;
        $with =       (isset($data['with'])) ? $data['with'] : [];
        $tags =       (isset($data['tags'])) ? $data['tags'] : [];
        $diff_time =  (isset($data['diff_time'])) ? $data['diff_time'] : null;
        $lang =       (isset($data['lang'])) ? $data['lang']->getLocale() : 'en_US'; // required, but still

        // build query
        $query = $this->createQueryBuilder('meal')
                      ->orderBy('meal.id', 'asc');
        $query = $this->filterByCategory($query, $category);
        $query = $this->filterByTags($query, $tags);
        $query = $this->filterByDiffTime($query, $diff_time);
        $query = $this->addProperties($query, $with);
        $query = $query->getQuery();

        // set language for EVERYTHING in query, instead of default
        $query->setHint(
            \Doctrine\ORM\Query::HINT_CUSTOM_OUTPUT_WALKER,
            'Gedmo\\Translatable\\Query\\TreeWalker\\TranslationWalker'
        );
        $query->setHint(\Gedmo\Translatable\TranslatableListener::HINT_TRANSLATABLE_LOCALE, $lang);

        // finally...
        $results = $query->getArrayResult();

        // format data (serializer?)
        $results = array_map(function($result) use ($diff_time) {
            // return $result;
            $status = 'created';
            if ($diff_time) {
                if ($result['deletedAt'] != null)
                    $status = 'deleted';
                elseif ($result['createdAt'] != $result['updatedAt'])
                    $status = 'modified';
            }

            $data = [
                'id' => $result['id'],
                'title' => $result['title'],
                'description' => $result['description'],
                'status' => $status,
            ];
            if (array_key_exists('tag', $result)) {
                foreach ($result['tag'] as $key => $tag) {
                    $tags[] = [
                        'id' => $tag['id'],
                        'title' => $tag['title'],
                        'slug' => $tag['slug'],
                    ];
                }
            }
            if (array_key_exists('category', $result)) {
                if ($result['category'] == null) {
                    $category = null;
                } else {
                    $category = [
                        'id' => $result['category']['id'],
                        'title' => $result['category']['title'],
                        'slug' => $result['category']['slug'],
                    ];
                }
            }
            if (array_key_exists('ingredient', $result)) {
                foreach ($result['ingredient'] as $key => $ingredient) {
                    $ingredients[] = [
                        'id' => $ingredient['id'],
                        'title' => $ingredient['title'],
                        'slug' => $ingredient['slug'],
                    ];
                }
            }

            if (array_key_exists('category', $result))
                $data['category'] = $category;
            if (array_key_exists('tag', $result))
                $data['tags'] = $tags;
            if (array_key_exists('ingredient', $result))
                $data['ingredients'] = $ingredients;

            return $data;
        }, $results);


        // and in the end...
        return $results;
    }

    /**
     * ADD PROPERTIES: tags, category, ingredients
     */
    private function addProperties($query, $with)
    {
        if (empty($with) || count($with) == 0) return $query;

        // tags
        if (in_array(1, $with)) {
            $query->leftJoin('meal.tag', 'meal_tags')
                  ->addSelect('meal_tags');
        }
        // category
        if (in_array(2, $with)) {
            $query->leftJoin('meal.category', 'meal_category')
                  ->addSelect('meal_category');
        }
        // ingredients
        if ( in_array(3, $with) ) {
            $query->leftJoin('meal.ingredient', 'meal_ingredients')
                  ->addSelect('meal_ingredients');
        }

        return $query;
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

        $query->innerJoin('meal.tag', 'tags')
              ->andWhere('tags.id in (:tags)')
              ->setParameter('tags', $tags)
              ->groupBy('meal.id')
              ->having('count(distinct tags.id) = :num_of_tags')
              ->setParameter('num_of_tags', count($tags));

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
