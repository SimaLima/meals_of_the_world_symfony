<?php

namespace App\Repository;

use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query;
use Gedmo\Translatable\TranslatableListener;

/**
 * @method Category|null find($id, $lockMode = null, $lockVersion = null)
 * @method Category|null findOneBy(array $criteria, array $orderBy = null)
 * @method Category[]    findAll()
 * @method Category[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

    /**
     * Get list of category options (for form)
     */
    public function getCategoryOptions($lang = 'en_US')
    {
        // define language
        if (!in_array($lang, ['hr_HR', 'de_DE', 'fr_FR'])) $lang = 'en_US';

        // query categories
        $query = $this->createQueryBuilder('categ')
                      ->orderBy('categ.id', 'asc')
                      ->getQuery();

        // set language for query
        $query->setHint(
                    Query::HINT_CUSTOM_OUTPUT_WALKER,
                    'Gedmo\\Translatable\\Query\\TreeWalker\\TranslationWalker'
                )
              ->setHint(TranslatableListener::HINT_TRANSLATABLE_LOCALE, $lang);

        $categories = $query->getArrayResult();
        $options = [];

        foreach ($categories as $category) {
            $options[$category['title']] = $category['id'];
        }

        $options['null'] = 'null';
        $options['!null'] = 'not_null';

        return $options;
    }

    // /**
    //  * @return Category[] Returns an array of Category objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Category
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
