<?php

namespace OHMedia\NewsBundle\Service;

use Doctrine\ORM\QueryBuilder;
use OHMedia\NewsBundle\Entity\Article;
use OHMedia\NewsBundle\Repository\ArticleRepository;
use OHMedia\PageBundle\Service\PageRawQuery;
use OHMedia\TimezoneBundle\Util\DateTimeUtil;
use OHMedia\UtilityBundle\Service\AbstractEntityPathProvider;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ArticleEntityPathProvider extends AbstractEntityPathProvider
{
    public function __construct(
        private ArticleRepository $articleRepository,
        private PageRawQuery $pageRawQuery,
        private UrlGeneratorInterface $urlGenerator,
        #[Autowire('%oh_media_news.page_template%')]
        private ?string $pageTemplate,
    ) {
    }

    public function getEntityClass(): string
    {
        return Article::class;
    }

    public function getGroupLabel(): string
    {
        return 'Articles';
    }

    public function getEntityQueryBuilder(?int $selectedEntityId): QueryBuilder
    {
        $qb = $this->articleRepository->createQueryBuilder('a')
            ->where('(a.published_at IS NOT NULL AND a.published_at <= :now)')
            ->setParameter('now', DateTimeUtil::getDateTimeUtc())
            ->orderBy('a.published_at', 'DESC');

        if ($selectedEntityId) {
            $qb->orWhere('a.id = :id')
                ->setParameter('id', $selectedEntityId);
        }

        return $qb;
    }

    public function getEntityPath(mixed $entity): ?string
    {
        if (!$entity->isPublished()) {
            return null;
        }

        $pagePath = $this->pageRawQuery->getPathWithTemplate($this->pageTemplate);

        if (!$pagePath) {
            return null;
        }

        return $this->urlGenerator->generate('oh_media_page_frontend', [
            'path' => $pagePath.'/'.$entity->getSlug(),
        ]);
    }
}
