<?php

namespace OHMedia\NewsBundle\Controller;

use OHMedia\BackendBundle\Form\MultiSaveType;
use OHMedia\BackendBundle\Routing\Attribute\Admin;
use OHMedia\NewsBundle\Entity\Article;
use OHMedia\NewsBundle\Entity\ArticleTag;
use OHMedia\NewsBundle\Form\ArticleTagType;
use OHMedia\NewsBundle\Repository\ArticleTagRepository;
use OHMedia\NewsBundle\Security\Voter\ArticleTagVoter;
use OHMedia\UtilityBundle\Form\DeleteType;
use OHMedia\UtilityBundle\Service\EntitySlugger;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Admin]
class ArticleTagBackendController extends AbstractController
{
    public function __construct(
        private EntitySlugger $entitySlugger,
    ) {
    }

    #[Route('/articles/tags', name: 'article_tag_index', methods: ['GET'])]
    public function index(ArticleTagRepository $articleTagRepository): Response
    {
        $newArticleTag = new ArticleTag();

        $this->denyAccessUnlessGranted(
            ArticleTagVoter::INDEX,
            $newArticleTag,
            'You cannot access the list of article tags.'
        );

        $qb = $articleTagRepository->createQueryBuilder('at')
            ->select('at')
            ->addSelect('(
                SELECT COUNT(a.id)
                FROM '.Article::class.' a
                JOIN a.tags t
                WHERE t.id = at.id
            ) AS article_count')
        ;

        $qb->orderBy('at.name', 'asc');

        return $this->render('@OHMediaNews/backend/article_tag/article_tag_index.html.twig', [
            'results' => $qb->getQuery()->getResult(),
            'new_article_tag' => $newArticleTag,
            'attributes' => $this->getAttributes(),
        ]);
    }

    #[Route('/articles/tag/create', name: 'article_tag_create', methods: ['GET', 'POST'])]
    public function create(
        Request $request,
        ArticleTagRepository $articleTagRepository
    ): Response {
        $articleTag = new ArticleTag();

        $this->denyAccessUnlessGranted(
            ArticleTagVoter::CREATE,
            $articleTag,
            'You cannot create a new article tag.'
        );

        $form = $this->createForm(ArticleTagType::class, $articleTag);

        $form->add('save', MultiSaveType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $this->setSlug($articleTag);

            if ($form->isValid()) {
                $articleTagRepository->save($articleTag, true);

                $this->addFlash('notice', 'The article tag was created successfully.');

                return $this->redirectForm($articleTag, $form);
            }

            $this->addFlash('error', 'There are some errors in the form below.');
        }

        return $this->render('@OHMediaNews/backend/article_tag/article_tag_create.html.twig', [
            'form' => $form->createView(),
            'article_tag' => $articleTag,
        ]);
    }

    #[Route('/articles/tag/{id}/edit', name: 'article_tag_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        #[MapEntity(id: 'id')] ArticleTag $articleTag,
        ArticleTagRepository $articleTagRepository
    ): Response {
        $this->denyAccessUnlessGranted(
            ArticleTagVoter::EDIT,
            $articleTag,
            'You cannot edit this article tag.'
        );

        $form = $this->createForm(ArticleTagType::class, $articleTag);

        $form->add('save', MultiSaveType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $this->setSlug($articleTag);

            if ($form->isValid()) {
                $articleTagRepository->save($articleTag, true);

                $this->addFlash('notice', 'The article tag was updated successfully.');

                return $this->redirectForm($articleTag, $form);
            }

            $this->addFlash('error', 'There are some errors in the form below.');
        }

        return $this->render('@OHMediaNews/backend/article_tag/article_tag_edit.html.twig', [
            'form' => $form->createView(),
            'article_tag' => $articleTag,
        ]);
    }

    private function redirectForm(ArticleTag $articleTag, FormInterface $form): Response
    {
        $clickedButtonName = $form->getClickedButton()->getName() ?? null;

        if ('keep_editing' === $clickedButtonName) {
            return $this->redirectToRoute('article_tag_edit', [
                'id' => $articleTag->getId(),
            ]);
        } elseif ('add_another' === $clickedButtonName) {
            return $this->redirectToRoute('article_tag_create');
        } else {
            return $this->redirectToRoute('article_tag_index');
        }
    }

    #[Route('/articles/tag/{id}/delete', name: 'article_tag_delete', methods: ['GET', 'POST'])]
    public function delete(
        Request $request,
        #[MapEntity(id: 'id')] ArticleTag $articleTag,
        ArticleTagRepository $articleTagRepository
    ): Response {
        $this->denyAccessUnlessGranted(
            ArticleTagVoter::DELETE,
            $articleTag,
            'You cannot delete this article tag.'
        );

        $form = $this->createForm(DeleteType::class, null);

        $form->add('delete', SubmitType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $articleTagRepository->remove($articleTag, true);

                $this->addFlash('notice', 'The article tag was deleted successfully.');

                return $this->redirectToRoute('article_tag_index');
            }

            $this->addFlash('error', 'There are some errors in the form below.');
        }

        return $this->render('@OHMediaNews/backend/article_tag/article_tag_delete.html.twig', [
            'form' => $form->createView(),
            'article_tag' => $articleTag,
        ]);
    }

    private function setSlug(ArticleTag $articleTag): void
    {
        $this->entitySlugger->setSlug($articleTag, $articleTag->getName());
    }

    private function getAttributes(): array
    {
        return [
            'view' => ArticleTagVoter::VIEW,
            'create' => ArticleTagVoter::CREATE,
            'delete' => ArticleTagVoter::DELETE,
            'edit' => ArticleTagVoter::EDIT,
        ];
    }
}
