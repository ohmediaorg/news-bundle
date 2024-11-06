<?php

namespace OHMedia\NewsBundle\Repository;

use OHMedia\NewsBundle\Entity\ArticleTag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ArticleTag|null find($id, $lockMode = null, $lockVersion = null)
 * @method ArticleTag|null findOneBy(array $criteria, array $orderBy = null)
 * @method ArticleTag[]    findAll()
 * @method ArticleTag[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArticleTagRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ArticleTag::class);
    }

    public function save(ArticleTag $articleTag, bool $flush = false): void
    {
        $this->getEntityManager()->persist($articleTag);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ArticleTag $articleTag, bool $flush = false): void
    {
        $this->getEntityManager()->remove($articleTag);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
