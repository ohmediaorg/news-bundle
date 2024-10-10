<?php

namespace OHMedia\NewsBundle\Controller;

use OHMedia\BackendBundle\Routing\Attribute\Admin;
use OHMedia\NewsBundle\Entity\Article;
use OHMedia\NewsBundle\Repository\ArticleRepository;
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
    ): Response {
        // Arbitrary limit to keep the feed manageable
        $feedLimit = 20;
        $articles = $articleRepository->getPublishedArticles()
            ->setMaxResults($feedLimit)
            ->getQuery()
            ->getResult();

        return $this->render('@OHMediaNews/frontend/rss.html.twig', [
            'articles' => $articles,
            'web_root' => $request->getSchemeAndHttpHost(),
            'settings' => [
                'title' => $settings->get(Article::SETTING_RSS_TITLE),
                'desc' => $settings->get(Article::SETTING_RSS_DESC),
            ],
        ],
            new Response('', Response::HTTP_OK, ['Content-Type' => 'application/rss+xml'])
        );
    }
}
