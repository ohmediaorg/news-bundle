<?php

namespace OHMedia\NewsBundle\Controller;

use OHMedia\NewsBundle\Entity\Article;
use OHMedia\NewsBundle\Repository\ArticleRepository;
use OHMedia\PageBundle\Service\PageRawQuery;
use OHMedia\SettingsBundle\Service\Settings;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ArticleRssFrontendController extends AbstractController
{
    #[Route('/news/rss', name: 'news_rss')]
    public function rssFeed(
        ArticleRepository $articleRepository,
        Settings $settings,
        PageRawQuery $pageRawQuery,
        #[Autowire('%oh_media_news.page_template%')]
        ?string $pageTemplate,
    ): Response {
        $parent = $pageRawQuery->getPathWithShortcodeOrTemplate(
            'news()',
            $pageTemplate,
        );

        // News not active on the site
        if (!$parent) {
            throw $this->createNotFoundException();
        }

        // Arbitrary limit to keep the feed manageable
        $feedLimit = 24;
        $articles = $articleRepository->createPublishedQueryBuilder()
            ->setMaxResults($feedLimit)
            ->getQuery()
            ->getResult();

        $response = $this->render('@OHMediaNews/frontend/rss.html.twig', [
            'articles' => $articles,
            'settings' => [
                'title' => $settings->get(Article::SETTING_RSS_TITLE),
                'desc' => $settings->get(Article::SETTING_RSS_DESC),
            ],
            'parent_page' => $parent,
        ]
        );

        $response->headers->set('Content-Type', 'application/rss+xml');

        return $response;
    }
}
