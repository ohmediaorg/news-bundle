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
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ArticleRssFrontendController extends AbstractController
{
    #[Route('/news/rss', name: 'news_rss')]
    public function rssFeed(
        Request $request,
        ArticleRepository $articleRepository,
        Settings $settings,
        PageRawQuery $pageRawQuery,
        UrlGeneratorInterface $urlGenerator,
    ): Response {
        // Arbitrary limit to keep the feed manageable
        $feedLimit = 24;
        $articleEntities = $articleRepository->getPublishedArticles()
            ->setMaxResults($feedLimit)
            ->getQuery()
            ->getResult();

        $webRoot = $request->getSchemeAndHttpHost();
        $parent = $pageRawQuery->getPathWithShortcode('news()');

        // News not active on the site
        if (!$parent) {
            return new Response('', Response::HTTP_NOT_FOUND);
        }

        $articles = [];
        foreach ($articleEntities as $entity) {
            $url = $urlGenerator->generate(
                'oh_media_page_frontend',
                ['path' => '/'.$parent.'/'.$entity->getSlug()],
                UrlGeneratorInterface::ABSOLUTE_URL
            );

            $articles[] = [
                'id' => $entity->getId(),
                'title' => $entity->getTitle(),
                'snippet' => $entity->getSnippet(),
                'link' => $url,
                'datetime' => $entity->getPublishDatetime(),
            ];
        }

        $response = $this->render('@OHMediaNews/frontend/rss.html.twig', [
            'articles' => $articles,
            'web_root' => $webRoot,
            'settings' => [
                'title' => $settings->get(Article::SETTING_RSS_TITLE),
                'desc' => $settings->get(Article::SETTING_RSS_DESC),
            ],
        ],
            new Response('', Response::HTTP_OK, ['Content-Type' => 'application/rss+xml'])
        );

        $response->headers->set('Content-Type', 'application/rss+xml');
        return $response;
    }
}
