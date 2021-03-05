<?php

namespace App\Repository;

use App\Entity\Meal;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

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
        dump($data);
        $per_page =    ($data['per_page']) ? $data['per_page'] : 5;
        $page =        ($data['page']) ? $data['page'] : null;
        $category =    ($data['category']) ? $data['category'] : null;
        $with =        ($data['with']) ? $data['with'] : [];
        $tags =        ($data['tags']) ? $data['tags'] : [];
        $lang =         $data['lang'];
        $diff_time =   ($data['diff_time']) ? $data['diff_time'] : null;

        $query = $this->createQueryBuilder('m')
                      ->orderBy('m.id', 'asc');

        // CATEGORY
        if ($category) {
            if (is_string($category)) $category = strtolower($category);
            if (is_numeric($category)) {
                $query->andWhere('m.category = :category')
                      ->setParameter('category', (int)$category);
            } elseif ($category == 'null') {
                $query->andWhere('m.category is NULL');
            } elseif ($category == '!null') {
                $query->andWhere('m.category is not NULL');
            }
        }

        // TAGS
        if (!empty($tags) && count($tags) > 0) {
            $query->innerJoin('m.tag', 't')
                  ->andWhere('t.id in (:tags)')
                  ->setParameter('tags', $tags)
                  ->groupBy('m.id')
                  ->having('count(distinct t.id) = :number')
                  ->setParameter('number', count($tags));
        }

        // WITH
        if (!empty($with)) {
            // dump($with);
            // if ( in_array(1, $with) ) {}
            // if ( in_array(2, $with) ) {}
            // if ( in_array(3, $with) ) {}
        }

        // DIFF_TIME
        if ($diff_time) {
            $diff_time = $diff_time->format('Y-m-d H:i:s');
            dump($diff_time);

            $this->getEntityManager()->getFilters()->disable('softdeleteable');

            $query->andWhere('m.createdAt > :diff_time')
                  ->setParameter('diff_time', $diff_time);
        } else {
            $this->getEntityManager()->getFilters()->enable('softdeleteable');
        }
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
