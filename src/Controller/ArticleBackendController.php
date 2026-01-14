<?php

namespace OHMedia\NewsBundle\Controller;

use Doctrine\ORM\QueryBuilder;
use OHMedia\BackendBundle\Form\MultiSaveType;
use OHMedia\BackendBundle\Routing\Attribute\Admin;
use OHMedia\BootstrapBundle\Service\Paginator;
use OHMedia\NewsBundle\Entity\Article;
use OHMedia\NewsBundle\Entity\ArticleTag;
use OHMedia\NewsBundle\Form\ArticleType;
use OHMedia\NewsBundle\Repository\ArticleRepository;
use OHMedia\NewsBundle\Security\Voter\ArticleTagVoter;
use OHMedia\NewsBundle\Security\Voter\ArticleVoter;
use OHMedia\TimezoneBundle\Util\DateTimeUtil;
use OHMedia\UtilityBundle\Form\DeleteType;
use OHMedia\UtilityBundle\Service\EntitySlugger;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Admin]
class ArticleBackendController extends AbstractController
{
    public function __construct(
        private EntitySlugger $entitySlugger,
    ) {
    }

    #[Route('/articles', name: 'article_index', methods: ['GET'])]
    public function index(
        ArticleRepository $articleRepository,
        Paginator $paginator,
        Request $request,
        #[Autowire('%oh_media_news.article_tags%')]
        bool $articleTagsEnabled,
    ): Response {
        $newArticle = new Article();
        $newArticleTag = new ArticleTag();

        $this->denyAccessUnlessGranted(
            ArticleVoter::INDEX,
            $newArticle,
            'You cannot access the list of articles.'
        );

        $qb = $articleRepository->createQueryBuilder('a');
        $qb->orderBy('CASE WHEN a.published_at IS NULL THEN 0 ELSE 1 END', 'ASC')
            ->addOrderBy('a.published_at', 'DESC');

        $searchForm = $this->getSearchForm($request);

        $this->applySearch($searchForm, $qb);

        return $this->render('@OHMediaNews/backend/article/article_index.html.twig', [
            'pagination' => $paginator->paginate($qb, 20),
            'new_article' => $newArticle,
            'new_article_tag' => $newArticleTag,
            'attributes' => $this->getAttributes(),
            'search_form' => $searchForm,
            'article_tags_enabled' => $articleTagsEnabled,
        ]);
    }

    private function getSearchForm(Request $request): FormInterface
    {
        $formBuilder = $this->container->get('form.factory')
            ->createNamedBuilder('', FormType::class, null, [
                'csrf_protection' => false,
            ]);

        $formBuilder->setMethod('GET');

        $formBuilder->add('search', SearchType::class, [
            'required' => false,
            'label' => 'Title, author, snippet, content',
        ]);

        $formBuilder->add('status', ChoiceType::class, [
            'required' => false,
            'choices' => [
                'All' => '',
                'Published' => 'published',
                'Scheduled' => 'scheduled',
                'Draft' => 'draft',
            ],
        ]);

        $form = $formBuilder->getForm();

        $form->handleRequest($request);

        return $form;
    }

    private function applySearch(FormInterface $form, QueryBuilder $qb): void
    {
        $search = $form->get('search')->getData();

        if ($search) {
            $searchFields = [
                'a.title',
                'a.slug',
                'a.author',
                'a.snippet',
                'a.content',
            ];

            $searchLikes = [];
            foreach ($searchFields as $searchField) {
                $searchLikes[] = "$searchField LIKE :search";
            }

            $qb->andWhere('('.implode(' OR ', $searchLikes).')')
                ->setParameter('search', '%'.$search.'%');
        }

        $status = $form->get('status')->getData();

        if ('published' === $status) {
            $qb->andWhere('a.published_at IS NOT NULL');
            $qb->andWhere('a.published_at <= :now');
            $qb->setParameter('now', DateTimeUtil::getDateTimeUtc());
        } elseif ('scheduled' === $status) {
            $qb->andWhere('a.published_at IS NOT NULL');
            $qb->andWhere('a.published_at > :now');
            $qb->setParameter('now', DateTimeUtil::getDateTimeUtc());
        } elseif ('draft' === $status) {
            $qb->andWhere('a.published_at IS NULL');
        }
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

        $form->add('save', MultiSaveType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $this->setSlug($article);

            if ($form->isValid()) {
                $articleRepository->save($article, true);

                $this->addFlash('notice', 'The article was created successfully.');

                return $this->redirectForm($article, $form);
            }

            $this->addFlash('error', 'There are some errors in the form below.');
        }

        return $this->render('@OHMediaNews/backend/article/article_create.html.twig', [
            'form' => $form->createView(),
            'article' => $article,
        ]);
    }

    #[Route('/article/{id}/edit', name: 'article_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        #[MapEntity(id: 'id')] Article $article,
        ArticleRepository $articleRepository
    ): Response {
        $this->denyAccessUnlessGranted(
            ArticleVoter::EDIT,
            $article,
            'You cannot edit this article.'
        );

        $form = $this->createForm(ArticleType::class, $article);

        $form->add('save', MultiSaveType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $this->setSlug($article);

            if ($form->isValid()) {
                $articleRepository->save($article, true);

                $this->addFlash('notice', 'The article was updated successfully.');

                return $this->redirectForm($article, $form);
            }

            $this->addFlash('error', 'There are some errors in the form below.');
        }

        return $this->render('@OHMediaNews/backend/article/article_edit.html.twig', [
            'form' => $form->createView(),
            'article' => $article,
        ]);
    }

    private function redirectForm(Article $article, FormInterface $form): Response
    {
        $clickedButtonName = $form->getClickedButton()->getName() ?? null;

        if ('keep_editing' === $clickedButtonName) {
            return $this->redirectToRoute('article_edit', [
                'id' => $article->getId(),
            ]);
        } elseif ('add_another' === $clickedButtonName) {
            return $this->redirectToRoute('article_create');
        } else {
            return $this->redirectToRoute('article_index');
        }
    }

    #[Route('/article/{id}/delete', name: 'article_delete', methods: ['GET', 'POST'])]
    public function delete(
        Request $request,
        #[MapEntity(id: 'id')] Article $article,
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

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $articleRepository->remove($article, true);

                $this->addFlash('notice', 'The article was deleted successfully.');

                return $this->redirectToRoute('article_index');
            }

            $this->addFlash('error', 'There are some errors in the form below.');
        }

        return $this->render('@OHMediaNews/backend/article/article_delete.html.twig', [
            'form' => $form->createView(),
            'article' => $article,
        ]);
    }

    private function setSlug(Article $article): void
    {
        $this->entitySlugger->setSlug($article, $article->getTitle());
    }

    private function getAttributes(): array
    {
        return [
            'view' => ArticleVoter::VIEW,
            'create' => ArticleVoter::CREATE,
            'delete' => ArticleVoter::DELETE,
            'edit' => ArticleVoter::EDIT,
            'view_tags' => ArticleTagVoter::VIEW,
        ];
    }
}
