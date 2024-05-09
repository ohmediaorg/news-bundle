<?php

namespace OHMedia\NewsBundle\Controller\Frontend;

use Doctrine\ORM\QueryBuilder;
use OHMedia\BootstrapBundle\Service\Paginator;
use OHMedia\MetaBundle\Entity\Meta;
use OHMedia\NewsBundle\Entity\Article;
use OHMedia\NewsBundle\Repository\ArticleRepository;
use OHMedia\NewsBundle\Repository\ArticleTagRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ArticleFrontendController extends AbstractController
{
    // TODO how do we handle the parent routes?
    // Can we replace this with names?
    public const PARENT_PATH = 'news';

    private function searchFilter(QueryBuilder $qb, string $search)
    {
        $searchFields = [
            'a.title',
            'a.content',
            'a.author',
        ];
        $ors = [];

        foreach ($searchFields as $searchField) {
            $ors[] = "$searchField LIKE :search";
        }

        return $qb->andWhere('('.implode(' OR ', $ors).')')
            ->setParameter('search', '%'.$search.'%');
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

    private static function cleanStrings(string $string): string
    {
        $chars = [
            "\n",
            "\r",
            "\t",
        ];

        return trim(
            strip_tags(
                str_replace($chars, '', $string)
            )
        );
    }

    // TODO see Article Tag Enable/Disable
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

    // TODO footer? Twig function? YES
    private function schema(Article $article, string $webRoot): string
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $article->getTitle(),
            'datePublished' => $article->getPublishDatetime()->format(\DateTime::ATOM),
            'dateModified' => $article->getUpdatedAt()->format(\DateTime::ATOM),
            'image' => [],
            'text' => self::cleanStrings($article->getContent()), // TODO can I use the snippet instead?
        ];
        $author = $article->getAuthor();
        $image = $article->getImage();

        // TODO confirm that author is optional
        if (!empty($author)) {
            $schema['author'] = [
                '@type' => 'Person',
                'name' => $article->getAuthor(),
            ];
        }

        if (!empty($image)) {
            $schema['image'] = [
                '@type' => 'ImageObject',
                'url' => $webRoot.'/'.$image->getPath(), // TODO not sure this is correct
                'width' => $image->getWidth(),
                'height' => $image->getHeight(),
            ];
        }

        // TODO try <script type="application/ld+json">{{ schema|json_encode|raw }}</script>
        return '<script type="application/ld+json">'.json_encode($schema, JSON_UNESCAPED_SLASHES).'</script>';
    }

    // TODO can I instead have two routes on the main listing? Retthink this
    //  -- Maybe tags should be with the search form instead?
    // TODO - Maybe this should instead be twig functions? YES
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

        $searchForm = $this->getSearchForm($request);
        $search = $searchForm->get('search')->getData();

        $tags = $this->getTags($articleTagRepository, $tagSlug);

        $qb = $this->getListingQueryBuilder($articleRepository);

        $qb->join('a.tags', 't')
            ->andWhere('t.slug = :tagSlug')
            ->setParameter('tagSlug', $tagSlug);

        if ($search) {
            $qb = $this->searchFilter($qb, $search);
        }

        // TODO if empty

        return $this->render('@OHMediaNews/article_listing.html.twig', [
            'pagination' => $paginator->paginate($qb, 8),
            'parent_path' => self::PARENT_PATH,
            'tags' => $tags,
            'searchForm' => $searchForm->createView(),
        ]);
    }

    #[Route('/'.self::PARENT_PATH, name: 'news_listing')]
    public function listing(
        Request $request,
        Paginator $paginator,
        ArticleRepository $articleRepository,
        ArticleTagRepository $articleTagRepository
    ): Response {
        $searchForm = $this->getSearchForm($request);
        $search = $searchForm->get('search')->getData();

        $qb = $this->getListingQueryBuilder($articleRepository);

        $tags = $this->getTags($articleTagRepository);

        if ($search) {
            $qb = $this->searchFilter($qb, $search);
        }

        return $this->render('@OHMediaNews/article_listing.html.twig', [
            'pagination' => $paginator->paginate($qb, 8),
            'parent_path' => self::PARENT_PATH,
            'tags' => $tags,
            'searchForm' => $searchForm->createView(),
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

        // TODO check comment on page bundle
        $meta = (new Meta())
            ->setTitle($article->getTitle())
            ->setDescription($article->getSnippet())
            ->setAppendBaseTitle(true); // TODO not sure what this does

        $schema = $this->schema($article, $request->getSchemeAndHttpHost());

        return $this->render('@OHMediaNews/article_item.html.twig', [
            'parent_path' => self::PARENT_PATH,
            'article' => $article,
            'meta_setting' => $meta,
            'schema' => $schema,
        ]);
    }
}
