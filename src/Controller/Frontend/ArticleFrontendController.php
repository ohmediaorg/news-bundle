<?php

namespace OHMedia\NewsBundle\Controller\Frontend;

use Doctrine\ORM\QueryBuilder;
use OHMedia\BootstrapBundle\Service\Paginator;
use OHMedia\MetaBundle\Entity\Meta;
use OHMedia\NewsBundle\Entity\Article;
use OHMedia\NewsBundle\Entity\ArticleTag;
use OHMedia\NewsBundle\Repository\ArticleRepository;
use OHMedia\NewsBundle\Repository\ArticleTagRepository;
use OHMedia\NewsBundle\Security\Voter\ArticleTagVoter;
use OHMedia\SettingsBundle\Service\Settings;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ArticleFrontendController extends AbstractController
{
    private $tagsEnabled;

    private function areTagsEnabled(): bool
    {
        if (!isset($this->tagsEnabled)) {
            $this->tagsEnabled = $this->isGranted(ArticleTagVoter::INDEX, new ArticleTag());
        }

        return $this->tagsEnabled;
    }

    private function getSearchForm(Request $request): FormInterface
    {
        $formBuilder = $this->container->get('form.factory')
            ->createNamedBuilder('', FormType::class, null, [
                'csrf_protection' => false,
            ]);

        $formBuilder
        ->setMethod('GET')
        ->add('search', TextType::class, [
            'label' => 'Search',
            'required' => false,
        ]);

        $form = $formBuilder->getForm();

        $form->handleRequest($request);

        return $form;
    }

    private function getListingQueryBuilder(ArticleRepository $articleRepository): QueryBuilder
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

        foreach ($tags as $index => $tag) {
            $tags[$index]->active = $tag->getSlug() === $tagSlug;
        }

        return $tags;
    }

    #[Route('/tag/{tagSlug}', name: 'news_tag_listing')]
    #[Route('/', name: 'news_listing')]
    public function listing(
        Request $request,
        Paginator $paginator,
        ArticleRepository $articleRepository,
        ArticleTagRepository $articleTagRepository,
        string $tagSlug = ''
    ): Response {
        $searchForm = $this->getSearchForm($request);
        $search = $searchForm->get('search')->getData();
        $tags = [];
        $qb = $this->getListingQueryBuilder($articleRepository);

        // TODO tag listing can be seperated out. Seperate controller?
        if ($this->areTagsEnabled()) {
            $tags = $this->getTags($articleTagRepository, $tagSlug);

            if ($tagSlug) {
                $qb->join('a.tags', 't')
                ->andWhere('t.slug = :tagSlug')
                ->setParameter('tagSlug', $tagSlug);
            }
        }

        if ($search) {
            $searchFields = [
                'a.title',
                'a.content',
                'a.author',
            ];
            $ors = [];

            foreach ($searchFields as $searchField) {
                $ors[] = "$searchField LIKE :search";
            }

            $qb->andWhere('('.implode(' OR ', $ors).')')
                ->setParameter('search', '%'.$search.'%');
        }

        return $this->render('@OHMediaNews/article_listing.html.twig', [
            'pagination' => $paginator->paginate($qb, 8),
            'tags' => $tags,
            'search_form' => $searchForm->createView(),
            'route_name' => $tagSlug ? 'news_tag_listing' : 'news_listing',
            'route_params' => $tagSlug ? ['tagSlug' => $tagSlug] : [],
        ]);
    }

    #[Route('/rss', name: 'news_rss')]
    public function rssFeed(
        Request $request,
        ArticleRepository $articleRepository,
        Settings $settings,
        string $routePrefix,
    ): Response {
        // TODO limit could be container param?
        $limit = 10;

        $articles = $articleRepository->createQueryBuilder('a')
            ->where('a.publish_datetime IS NOT NULL')
            ->orderBy('a.publish_datetime', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return $this->render('@OHMediaNews/frontend/rss.html.twig', [
            'articles' => $articles,
            'web_root' => $request->getSchemeAndHttpHost(),
            'settings' => [
                'title' => $settings->get(Article::SETTING_RSS_TITLE),
                'desc' => $settings->get(Article::SETTING_RSS_DESC),
            ],
            'routePrefix' => $routePrefix,
        ],
            new Response('', Response::HTTP_OK, ['Content-Type' => 'application/rss+xml'])
        );
    }

    #[Route('/{slug}', name: 'news_item')]
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

        // TODO check comment on page bundle
        $meta = (new Meta())
            ->setTitle($article->getTitle())
            ->setDescription($article->getSnippet())
            ->setAppendBaseTitle(true); // TODO not sure what this does

        return $this->render('@OHMediaNews/article_item.html.twig', [
            'article' => $article,
            'meta_setting' => $meta,
            'web_root' => $request->getSchemeAndHttpHost(),
        ]);
    }
}
