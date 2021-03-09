<?php

namespace App\Repository;

use App\Entity\Tag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Gedmo\Translatable\TranslatableListener;
use Doctrine\ORM\Query;

/**
 * @method Tag|null find($id, $lockMode = null, $lockVersion = null)
 * @method Tag|null findOneBy(array $criteria, array $orderBy = null)
 * @method Tag[]    findAll()
 * @method Tag[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TagRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tag::class);
    }

    /**
     * Get list of tag options (for form)
     */
    public function getTagOptions($lang = 'en_US')
    {
        // define language
        if (!in_array($lang, ['hr_HR', 'de_DE', 'fr_FR'])) $lang = 'en_US';

        // query tags
        $query = $this->createQueryBuilder('tag')
                      ->orderBy('tag.id', 'asc')
                      ->getQuery();

        // set language for query
        $query->setHint(
                    Query::HINT_CUSTOM_OUTPUT_WALKER,
                    'Gedmo\\Translatable\\Query\\TreeWalker\\TranslationWalker'
                )
              ->setHint(TranslatableListener::HINT_TRANSLATABLE_LOCALE, $lang);

        $tags = $query->getArrayResult();
        $options = [];

        foreach ($tags as $tag) {
            $options[$tag['title']] = $tag['id'];
        }

        return $options;
    }


    // /**
    //  * @return Tag[] Returns an array of Tag objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Tag
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
