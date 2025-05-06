<?php

namespace OHMedia\NewsBundle\Twig;

use OHMedia\NewsBundle\Repository\ArticleRepository;
use OHMedia\PageBundle\Service\PageRawQuery;
use OHMedia\TimezoneBundle\Service\Timezone;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class RecentArticlesExtension extends AbstractExtension
{
    private $timezone;

    public function __construct(
        private ArticleRepository $articleRepository,
        private PageRawQuery $pageRawQuery,
        Timezone $timezoneService,
        #[Autowire('%oh_media_news.page_template%')]
        private ?string $pageTemplate,
    ) {
        $this->timezone = new \DateTimeZone($timezoneService->get());
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

        foreach ($articles as $article) {
            $article->setTimezone($this->timezone);
        }

        $pagePath = $this->pageRawQuery->getPathWithTemplate($this->pageTemplate);

        return $twig->render('@OHMediaNews/recent_news.html.twig', [
            'articles' => $articles,
            'news_page_path' => $pagePath,
        ]);
    }
}
