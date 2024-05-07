<?php

namespace OHMedia\NewsBundle\Controller\Frontend;

use OHMedia\BootstrapBundle\Service\Paginator;
use OHMedia\NewsBundle\Repository\ArticleTagRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ArticleTagFrontendController extends AbstractController
{
    public function tags(
        Request $request,
        ArticleTagRepository $articleTagRepository
    ): Response {
        $qb = $articleTagRepository->createQueryBuilder('a')
            ->orderBy('a.name', 'ASC');

        return $this->render('@OHMediaNews/article_tags.html.twig', [

        ]);
    }
}
