<?php

namespace OHMedia\NewsBundle\Controller;

use OHMedia\BootstrapBundle\Service\Paginator;
use OHMedia\NewsBundle\Repository\ArticleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ArticleFrontendController extends AbstractController
{
    #[Route('/news', name: 'news')]
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
        ]);
    }
}
