<?php

namespace OHMedia\NewsBundle\Service;

use OHMedia\NewsBundle\Repository\ArticleRepository;
use OHMedia\PageBundle\Service\PageRawQuery;
use OHMedia\PageBundle\Sitemap\AbstractSitemapUrlProvider;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class ArticleSitemapUrlProvider extends AbstractSitemapUrlProvider
{
    public function __construct(
        private ArticleRepository $articleRepository,
        private PageRawQuery $pageRawQuery,
        #[Autowire('%oh_media_news.page_template%')]
        private ?string $pageTemplate,
    ) {
    }

    protected function buildSitemapUrls(): void
    {
        $pagePath = $this->pageRawQuery->getPathWithShortcodeOrTemplate(
            'news()',
            $this->pageTemplate,
        );

        if (!$pagePath) {
            return;
        }

        $articles = $this->articleRepository->createPublishedQueryBuilder()
            ->getQuery()
            ->getResult();

        foreach ($articles as $article) {
            $this->addSitemapUrl(
                $pagePath.'/'.$article->getSlug(),
                $article->getUpdatedAt()
            );
        }
    }
}
