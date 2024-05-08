<?php

namespace OHMedia\NewsBundle\Controller\Frontend;

use OHMedia\BootstrapBundle\Service\Paginator;
use OHMedia\MetaBundle\Entity\Meta;
use OHMedia\NewsBundle\Entity\Article;
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
                'url' => $webRoot.'/'.$image->getPath(), //TODO not sure this is correct
                'width' => $image->getWidth(),
                'height' => $image->getHeight(),
            ];
        }

        // TODO try <script type="application/ld+json">{{ schema|json_encode|raw }}</script>
        return '<script type="application/ld+json">'.json_encode($schema, JSON_UNESCAPED_SLASHES).'</script>';
    }

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

        // TODO check comment on page bundle
        $meta = (new Meta())
            ->setTitle($article->getTitle())
            ->setDescription($article->getSnippet())
            ->setAppendBaseTitle(true); // TODO not sure what this does

        $schema = $this->schema($article, $request->getSchemeAndHttpHost());

        return $this->render('@OHMediaNews/frontend/article_item.html.twig', [
            'parent_path' => self::PARENT_PATH,
            'article' => $article,
            'meta_setting' => $meta,
            'schema' => $schema,
        ]);
    }
}
