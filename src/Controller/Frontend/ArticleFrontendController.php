<?php

namespace OHMedia\NewsBundle\Controller\Frontend;

use OHMedia\BootstrapBundle\Service\Paginator;
use OHMedia\NewsBundle\Repository\ArticleRepository;
use OHMedia\NewsBundle\Repository\ArticleTagRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ArticleFrontendController extends AbstractController
{
    // TODO how do we handle the parent routes?
    public const PARENT_PATH = 'news';

    private function getListingQueryBuilder(ArticleRepository $articleRepository): \Doctrine\ORM\QueryBuilder
    {
        return $articleRepository->createQueryBuilder('a')
            ->where('a.publish_datetime IS NOT NULL')
            ->orderBy('a.publish_datetime', 'DESC');
    }

    private function getTags(ArticleTagRepository $articleTagRepository, string $tagSlug = ''): array
    {
        $tags = $articleTagRepository->createQueryBuilder('t')
            ->join('t.articles', 'a')
            ->getQuery()
            ->getResult();

        foreach($tags as $index => $tag) {
            $tags[$index]->active = $tag->getSlug() === $tagSlug;
        }

        return $tags;
    }

    #[Route('/news/tag/{tagSlug}', name: 'news_tag_listing')]
    public function tagListing(
        Request $request,
        Paginator $paginator,
        ArticleRepository $articleRepository,
        ArticleTagRepository $articleTagRepository,
        string $tagSlug = ''
    ): Response {
        if (!$tagSlug) {
            // TODO not found
        }

        $tags = $this->getTags($articleTagRepository, $tagSlug);
        $qb = $this->getListingQueryBuilder($articleRepository);

        $qb->join('a.tags', 't')
            ->andWhere('t.slug = :tagSlug')
            ->setParameter('tagSlug', $tagSlug);

        // TODO if empty

        return $this->render('@OHMediaNews/frontend/article_listing.html.twig', [
            'pagination' => $paginator->paginate($qb, 8),
            'parent_path' => self::PARENT_PATH,
            'tags' => $tags,
        ]);
    }

    #[Route('/'.self::PARENT_PATH, name: 'news_listing')]
    public function listing(
        Request $request,
        Paginator $paginator,
        ArticleRepository $articleRepository,
        ArticleTagRepository $articleTagRepository
    ): Response {
        $qb = $this->getListingQueryBuilder($articleRepository);

        $tags = $this->getTags($articleTagRepository);

        return $this->render('@OHMediaNews/frontend/article_listing.html.twig', [
            'pagination' => $paginator->paginate($qb, 8),
            'parent_path' => self::PARENT_PATH,
            'tags' => $tags,
        ]);
    }

    #[Route('/'.self::PARENT_PATH.'/{slug}', name: 'news_item')]
    public function item(
        Request $request,
        ArticleRepository $articleRepository,
        string $slug = ''
    ): Response {
        $qb = $articleRepository->createQueryBuilder('a');
        // TODO is there a filter equivalant for published?
        $article = $qb->where('a.publish_datetime IS NOT NULL')
            ->andWhere('a.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$article) {
            // TODO not found
        }

        return $this->render('@OHMediaNews/frontend/article_item.html.twig', [
            'parent_path' => self::PARENT_PATH,
            'article' => $article,
        ]);
    }
}
