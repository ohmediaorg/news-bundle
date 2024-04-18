<?php

namespace OHMedia\NewsBundle\Controller\Backend;

use OHMedia\NewsBundle\Entity\Article;
use OHMedia\NewsBundle\Form\ArticleType;
use OHMedia\NewsBundle\Repository\ArticleRepository;
use OHMedia\NewsBundle\Security\Voter\ArticleVoter;
use OHMedia\BackendBundle\Routing\Attribute\Admin;
use OHMedia\BootstrapBundle\Service\Paginator;
use OHMedia\SecurityBundle\Form\DeleteType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Admin]
class ArticleController extends AbstractController
{
    #[Route('/articles', name: 'article_index', methods: ['GET'])]
    public function index(
        ArticleRepository $articleRepository,
        Paginator $paginator
    ): Response {
        $newArticle = new Article();

        $this->denyAccessUnlessGranted(
            ArticleVoter::INDEX,
            $newArticle,
            'You cannot access the list of articles.'
        );

        $qb = $articleRepository->createQueryBuilder('a');
        $qb->orderBy('a.id', 'desc');

        return $this->render('@backend/article/article_index.html.twig', [
            'pagination' => $paginator->paginate($qb, 20),
            'new_article' => $newArticle,
            'attributes' => $this->getAttributes(),
        ]);
    }

    #[Route('/article/create', name: 'article_create', methods: ['GET', 'POST'])]
    public function create(
        Request $request,
        ArticleRepository $articleRepository
    ): Response {
        $article = new Article();

        $this->denyAccessUnlessGranted(
            ArticleVoter::CREATE,
            $article,
            'You cannot create a new article.'
        );

        $form = $this->createForm(ArticleType::class, $article);

        $form->add('submit', SubmitType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $articleRepository->save($article, true);

            $this->addFlash('notice', 'The article was created successfully.');

            return $this->redirectToRoute('article_index');
        }

        return $this->render('@backend/article/article_create.html.twig', [
            'form' => $form->createView(),
            'article' => $article,
        ]);
    }

    #[Route('/article/{id}', name: 'article_view', methods: ['GET'])]
    public function view(Article $article): Response
    {
        $this->denyAccessUnlessGranted(
            ArticleVoter::VIEW,
            $article,
            'You cannot view this article.'
        );

        return $this->render('@backend/article/article_view.html.twig', [
            'article' => $article,
            'attributes' => $this->getAttributes(),
        ]);
    }

    #[Route('/article/{id}/edit', name: 'article_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Article $article,
        ArticleRepository $articleRepository
    ): Response {
        $this->denyAccessUnlessGranted(
            ArticleVoter::EDIT,
            $article,
            'You cannot edit this article.'
        );

        $form = $this->createForm(ArticleType::class, $article);

        $form->add('submit', SubmitType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $articleRepository->save($article, true);

            $this->addFlash('notice', 'The article was updated successfully.');

            return $this->redirectToRoute('article_view', [
                'id' => $article->getId(),
            ]);
        }

        return $this->render('@backend/article/article_edit.html.twig', [
            'form' => $form->createView(),
            'article' => $article,
        ]);
    }

    #[Route('/article/{id}/delete', name: 'article_delete', methods: ['GET', 'POST'])]
    public function delete(
        Request $request,
        Article $article,
        ArticleRepository $articleRepository
    ): Response {
        $this->denyAccessUnlessGranted(
            ArticleVoter::DELETE,
            $article,
            'You cannot delete this article.'
        );

        $form = $this->createForm(DeleteType::class, null);

        $form->add('delete', SubmitType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $articleRepository->remove($article, true);

            $this->addFlash('notice', 'The article was deleted successfully.');

            return $this->redirectToRoute('article_index');
        }

        return $this->render('@backend/article/article_delete.html.twig', [
            'form' => $form->createView(),
            'article' => $article,
        ]);
    }

    private function getAttributes(): array
    {
        return [
            'view' => ArticleVoter::VIEW,
            'create' => ArticleVoter::CREATE,
            'delete' => ArticleVoter::DELETE,
            'edit' => ArticleVoter::EDIT,
        ];
    }
}
