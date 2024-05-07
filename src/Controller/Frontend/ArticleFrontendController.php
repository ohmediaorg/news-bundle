<?php

namespace OHMedia\NewsBundle\Controller\Frontend;

use OHMedia\BootstrapBundle\Service\Paginator;
use OHMedia\NewsBundle\Repository\ArticleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ArticleFrontendController extends AbstractController
{
    // TODO how do we handle the parent routes?
    public const PARENT_PATH = 'news';

    #[Route('/'.self::PARENT_PATH, name: 'news_listing')]
    public function listing(
        Request $request,
        Paginator $paginator,
        ArticleRepository $articleRepository
    ): Response {
        $qb = $articleRepository->createQueryBuilder('a');
        // TODO is there a filter equivalant for published?
        $qb->where('a.publish_datetime IS NOT NULL')
            // ->where('a.publish_datetime <= NOW()') //TODO
            ->orderBy('a.publish_datetime', 'DESC');

        return $this->render('@OHMediaNews/article_listing.html.twig', [
            'pagination' => $paginator->paginate($qb, 8),
            'parent_path' => self::PARENT_PATH,
        ]);
    }

    #[Route('/'.self::PARENT_PATH.'/{slug}', name: 'news_item')]
    public function item(
        Request $request,
        ArticleRepository $articleRepository,
        string $slug
    ): Response {
        $qb = $articleRepository->createQueryBuilder('a');
        // TODO is there a filter equivalant for published?
        $article = $qb->where('a.publish_datetime IS NOT NULL')
            ->andWhere('a.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult();

        if(! $article) {
            // TODO not found
        }

        return $this->render('@OHMediaNews/article_item.html.twig', [
            'parent_path' => self::PARENT_PATH,
            'article' => $article,
        ]);
    }
}
