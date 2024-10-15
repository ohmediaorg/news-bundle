<?php

namespace OHMedia\NewsBundle\Controller;

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
        $articleEntities = $articleRepository->getPublishedArticles()
            ->setMaxResults($feedLimit)
            ->getQuery()
            ->getResult();

        // TODO
        $path = '/news';
        $webRoot = $request->getSchemeAndHttpHost();
        // $pagePath = $this->pageRawQuery->getPathWithShortcode('blog()');

        $articles = [];
        foreach ($articleEntities as $entity) {
            $articles[] = [
                'id' => $entity->getId(),
                'title' => $entity->getTitle(),
                'snippet' => $entity->getSnippet(),
                'link' => $webRoot.$path.'/'.$entity->getSlug(),
                'datetime' => $entity->getPublishDatetime(),
            ];
        }

        return $this->render('@OHMediaNews/frontend/rss.html.twig', [
            'articles' => $articles,
            'web_root' => $webRoot,
            'settings' => [
                'title' => $settings->get(Article::SETTING_RSS_TITLE),
                'desc' => $settings->get(Article::SETTING_RSS_DESC),
            ],
            'feed_url' => $webRoot.$path.'/rss',
        ],
            new Response('', Response::HTTP_OK, ['Content-Type' => 'application/rss+xml'])
        );
    }
}
