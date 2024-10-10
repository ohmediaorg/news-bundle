<?php

namespace OHMedia\NewsBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use OHMedia\NewsBundle\Entity\Article;
use OHMedia\TimezoneBundle\Util\DateTimeUtil;

/**
 * @method Article|null find($id, $lockMode = null, $lockVersion = null)
 * @method Article|null findOneBy(array $criteria, array $orderBy = null)
 * @method Article[]    findAll()
 * @method Article[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    public function save(Article $article, bool $flush = false): void
    {
        $this->getEntityManager()->persist($article);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Article $article, bool $flush = false): void
    {
        $this->getEntityManager()->remove($article);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getPublishedArticles(): QueryBuilder
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.publish_datetime IS NOT NULL')
            ->andWhere('a.publish_datetime <= :now')
            ->setParameter('now', DateTimeUtil::getDateTimeUtc());
    }

    // TODO containsWysiwygShortcodes
}
