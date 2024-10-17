<?php

namespace OHMedia\NewsBundle\Twig;

use OHMedia\NewsBundle\Repository\ArticleRepository;
use OHMedia\PageBundle\Service\PageRawQuery;
use Twig\Extension\AbstractExtension;
use Twig\Environment;
use Twig\TwigFunction;

class RecentArticlesExtension extends AbstractExtension
{
    public function __construct(
        private ArticleRepository $articleRepository,
        private PageRawQuery $pageRawQuery,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('recent_articles', [$this, 'recentArticles'], [
                'needs_environment' => true,
                'is_safe' => ['html'],
            ]),
        ];
    }

    public function recentArticles(Environment $twig, int $limit = 3): string
    {
        if ($limit <= 0) {
            $limit = 3;
        }

        $qb = $this->articleRepository->createPublishedQueryBuilder();
        $qb->setMaxResults($limit);

        $articles = $qb->getQuery()->getResult();

        $pagePath = $this->pageRawQuery->getPathWithShortcode('news()');

        return $twig->render('@OHMediaNews/recent_news.html.twig', [
            'articles' => $articles,
            'news_page_path' => $pagePath,
        ]);
    }
}
