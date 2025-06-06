<?php

namespace OHMedia\NewsBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use OHMedia\NewsBundle\Entity\Article;
use OHMedia\TimezoneBundle\Util\DateTimeUtil;
use OHMedia\WysiwygBundle\Repository\WysiwygRepositoryInterface;

/**
 * @method Article|null find($id, $lockMode = null, $lockVersion = null)
 * @method Article|null findOneBy(array $criteria, array $orderBy = null)
 * @method Article[]    findAll()
 * @method Article[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArticleRepository extends ServiceEntityRepository implements WysiwygRepositoryInterface
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

    public function createPublishedQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.published_at IS NOT NULL')
            ->andWhere('a.published_at <= :now')
            ->setParameter('now', DateTimeUtil::getDateTimeUtc())
            ->orderBy('a.published_at', 'DESC');
    }

    public function containsWysiwygShortcodes(string ...$shortcodes): bool
    {
        $ors = [];
        $params = new ArrayCollection();

        foreach ($shortcodes as $i => $shortcode) {
            $ors[] = 'a.content LIKE :shortcode_'.$i;
            $params[] = new Parameter('shortcode_'.$i, '%'.$shortcode.'%');
        }

        return $this->createQueryBuilder('a')
            ->select('COUNT(a)')
            ->where(implode(' OR ', $ors))
            ->setParameters($params)
            ->getQuery()
            ->getSingleScalarResult() > 0;
    }
}
