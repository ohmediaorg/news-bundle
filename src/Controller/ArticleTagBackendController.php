<?php

namespace OHMedia\NewsBundle\Controller\Backend;

use OHMedia\NewsBundle\Entity\ArticleTag;
use OHMedia\NewsBundle\Form\ArticleTagType;
use OHMedia\NewsBundle\Repository\ArticleTagRepository;
use OHMedia\NewsBundle\Security\Voter\ArticleTagVoter;
use OHMedia\BackendBundle\Routing\Attribute\Admin;
use OHMedia\BootstrapBundle\Service\Paginator;
use OHMedia\SecurityBundle\Form\DeleteType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\AsciiSlugger;

#[Admin]
class ArticleTagBackendController extends AbstractController
{
    #[Route('/articles/tags', name: 'article_tag_index', methods: ['GET'])]
    public function index(
        ArticleTagRepository $articleTagRepository,
        Paginator $paginator
    ): Response {
        $newArticleTag = new ArticleTag();

        $this->denyAccessUnlessGranted(
            ArticleTagVoter::INDEX,
            $newArticleTag,
            'You cannot access the list of article tags.'
        );

        $qb = $articleTagRepository->createQueryBuilder('at');
        $qb->orderBy('at.id', 'desc');

        return $this->render('@OHMediaNews/backend/article_tag/article_tag_index.html.twig', [
            'pagination' => $paginator->paginate($qb, 20),
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
        $slug = $articleTag->getSlug();

        $this->denyAccessUnlessGranted(
            ArticleTagVoter::CREATE,
            $articleTag,
            'You cannot create a new article tag.'
        );

        $form = $this->createForm(ArticleTagType::class, $articleTag);

        $form->add('submit', SubmitType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if(! $slug){
                $articleTag->setSlug(
                    $this->buildSlug($articleTagRepository, $articleTag->getId(), $articleTag->getName())
                );
            }

            $articleTagRepository->save($articleTag, true);

            $this->addFlash('notice', 'The article tag was created successfully.');

            return $this->redirectToRoute('article_tag_index');
        }

        return $this->render('@OHMediaNews/backend/article_tag/article_tag_create.html.twig', [
            'form' => $form->createView(),
            'article_tag' => $articleTag,
        ]);
    }

    #[Route('/articles/tag/{id}/edit', name: 'article_tag_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        ArticleTag $articleTag,
        ArticleTagRepository $articleTagRepository
    ): Response {
        $this->denyAccessUnlessGranted(
            ArticleTagVoter::EDIT,
            $articleTag,
            'You cannot edit this article tag.'
        );

        $slug = $articleTag->getSlug();
        $form = $this->createForm(ArticleTagType::class, $articleTag);

        $form->add('submit', SubmitType::class);

        $form->handleRequest($request);
        //TODO Q - Do we want to avoid duplicated code on these methods?
        if ($form->isSubmitted() && $form->isValid()) {
            if(! $slug){
                $articleTag->setSlug(
                    $this->buildSlug($articleTagRepository, $articleTag->getId(), $articleTag->getName())
                );
            }

            $articleTagRepository->save($articleTag, true);

            $this->addFlash('notice', 'The article tag was updated successfully.');

            return $this->redirectToRoute('article_tag_index', [
                'id' => $articleTag->getId(),
            ]);
        }

        return $this->render('@OHMediaNews/backend/article_tag/article_tag_edit.html.twig', [
            'form' => $form->createView(),
            'article_tag' => $articleTag,
        ]);
    }

    #[Route('/articles/tag/{id}/delete', name: 'article_tag_delete', methods: ['GET', 'POST'])]
    public function delete(
        Request $request,
        ArticleTag $articleTag,
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

        if ($form->isSubmitted() && $form->isValid()) {
            $articleTagRepository->remove($articleTag, true);

            $this->addFlash('notice', 'The article tag was deleted successfully.');

            return $this->redirectToRoute('article_tag_index');
        }

        return $this->render('@OHMediaNews/backend/article_tag/article_tag_delete.html.twig', [
            'form' => $form->createView(),
            'article_tag' => $articleTag,
        ]);
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

    // TODO - This is outdated
    private function buildSlug(ArticleTagRepository $articleTagRepository, ?int $id, string $name): string
    {
        $slugger = new AsciiSlugger();
        $slug = $slugger->slug($name);

        $i = 1;
        while ($articleTagRepository->countBySlug($slug, $id)) {
            $slug = $slugger->slug($name.'-'.$i);

            ++$i;
        }

        return $slug;
    }
}
