<?php

namespace OHMedia\NewsBundle\Controller;

use OHMedia\NewsBundle\Entity\Article;
use OHMedia\NewsBundle\Repository\ArticleRepository;
use OHMedia\PageBundle\Service\PageRawQuery;
use OHMedia\SettingsBundle\Service\Settings;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ArticleRssFrontendController extends AbstractController
{
    #[Route('/news/rss', name: 'news_rss')]
    public function rssFeed(
        Request $request,
        ArticleRepository $articleRepository,
        Settings $settings,
        PageRawQuery $pageRawQuery,
    ): Response {
        $parent = $pageRawQuery->getPathWithShortcode('news()');

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
        ],
            new Response(null, Response::HTTP_OK, ['Content-Type' => 'application/rss+xml'])
        );

        $response->headers->set('Content-Type', 'application/rss+xml');

        return $response;
    }
}
