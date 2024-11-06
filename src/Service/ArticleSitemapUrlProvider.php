<?php

namespace OHMedia\NewsBundle\Service;

use OHMedia\NewsBundle\Repository\ArticleRepository;
use OHMedia\PageBundle\Service\PageRawQuery;
use OHMedia\PageBundle\Sitemap\AbstractSitemapUrlProvider;

class ArticleSitemapUrlProvider extends AbstractSitemapUrlProvider
{
    public function __construct(
        private ArticleRepository $articleRepository,
        private PageRawQuery $pageRawQuery,
    ) {
    }

    protected function buildSitemapUrls(): void
    {
        $pagePath = $this->pageRawQuery->getPathWithShortcode('news()');

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
