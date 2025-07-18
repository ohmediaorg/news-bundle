<?php

namespace OHMedia\NewsBundle\Twig;

use OHMedia\BootstrapBundle\Service\Paginator;
use OHMedia\FileBundle\Service\FileManager;
use OHMedia\MetaBundle\Entity\Meta;
use OHMedia\NewsBundle\Entity\Article;
use OHMedia\NewsBundle\Repository\ArticleRepository;
use OHMedia\NewsBundle\Repository\ArticleTagRepository;
use OHMedia\PageBundle\Event\DynamicPageEvent;
use OHMedia\PageBundle\Service\PageRenderer;
use OHMedia\SettingsBundle\Service\Settings;
use OHMedia\TimezoneBundle\Util\DateTimeUtil;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\UrlHelper;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

#[AsEventListener(event: DynamicPageEvent::class, method: 'onDynamicPageEvent')]
class NewsExtension extends AbstractExtension
{
    private ?Article $articleEntity = null;

    public function __construct(
        private ArticleRepository $articleRepository,
        private FileManager $fileManager,
        private PageRenderer $pageRenderer,
        private Paginator $paginator,
        private Settings $settings,
        private UrlHelper $urlHelper,
        private UrlGeneratorInterface $urlGenerator,
        private ArticleTagRepository $articleTagRepository,
        #[Autowire('%oh_media_news.article_tags%')]
        private bool $articleTagsEnabled,
        private RequestStack $requestStack,
        #[Autowire('%oh_media_news.page_template%')]
        private ?string $pageTemplate,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('news', [$this, 'news'], [
                'needs_environment' => true,
                'is_safe' => ['html'],
            ]),
        ];
    }

    public function onDynamicPageEvent(DynamicPageEvent $dynamicPageEvent)
    {
        $pageRevision = $this->pageRenderer->getCurrentPageRevision();

        $isTemplate = $pageRevision->isTemplate($this->pageTemplate);

        if (!$isTemplate && !$pageRevision->containsShortcode('news()')) {
            return;
        }

        $dynamicPageEvent->stopPropagation();

        $dynamicPart = $this->pageRenderer->getDynamicPart();

        $qb = $this->articleRepository->createPublishedQueryBuilder();
        $qb->andWhere('a.slug = :slug');
        $qb->setParameter('slug', $dynamicPart);
        $qb->setMaxResults(1);

        $this->articleEntity = $qb->getQuery()->getOneOrNullResult();

        if (!$this->articleEntity) {
            throw new NotFoundHttpException('Article not found.');
        }

        $meta = new Meta();
        $meta->setTitle($this->articleEntity->getTitle());
        $meta->setDescription($this->articleEntity->getSnippet());
        $meta->setImage($this->articleEntity->getImage());
        $meta->setAppendBaseTitle(true);

        $this->pageRenderer->setDynamicMeta($meta);

        $pagePath = $this->pageRenderer->getCurrentPage()->getPath();

        $this->pageRenderer->addDynamicBreadcrumb(
            $this->articleEntity->getTitle(),
            $pagePath.'/'.$dynamicPart
        );
    }

    public function news(Environment $twig): string
    {
        $pagePath = $this->pageRenderer->getCurrentPage()->getPath();

        if ($this->articleEntity) {
            $content = $twig->render('@OHMediaNews/news_item.html.twig', [
                'article' => $this->articleEntity,
                'news_page_path' => $pagePath,
            ]);

            $content .= $this->getSchema($this->articleEntity);

            return $content;
        }

        $qb = $this->articleRepository->createPublishedQueryBuilder();

        $tags = null;
        $request = $this->requestStack->getCurrentRequest();
        $query = $request->query->all();
        $activeTags = [];

        if ($this->articleTagsEnabled) {
            // accommodates multiple tags (ie. `tags[]=abc&tags[]=123`)
            $activeTags = isset($query['tags']) && is_array($query['tags']) ?
                $query['tags'] :
                [];

            if ($activeTags) {
                $qb->innerJoin('a.tags', 't');
                $qb->andWhere('t.slug IN (:tags)');
                $qb->setParameter('tags', $activeTags);
            }
        }

        $pagination = $this->paginator->paginate($qb, 12);

        return $twig->render('@OHMediaNews/news_listing.html.twig', [
            'pagination' => $pagination,
            'news_page_path' => $pagePath,
            'tags' => $this->getTagsArray($query, $activeTags, $pagePath),
        ]);
    }

    private function getTagsArray(
        array $query,
        array $activeTags,
        string $pagePath
    ): array {
        if (!$this->articleTagsEnabled) {
            return [];
        }

        $pageHref = $this->urlGenerator->generate(
            'oh_media_page_frontend',
            ['path' => $pagePath],
        );

        $tagsArray = [];

        $tags = $this->articleTagRepository->createQueryBuilder('at')
            ->select('at')
            ->innerJoin('at.articles', 'a')
            ->where('a.published_at IS NOT NULL')
            ->andWhere('a.published_at <= :now')
            ->setParameter('now', DateTimeUtil::getDateTimeUtc())
            ->getQuery()
            ->getResult();

        foreach ($tags as $tag) {
            $slug = $tag->getSlug();

            // making copies for modification
            $thisQuery = $query;
            $thisQueryTags = $activeTags;

            $key = array_search($slug, $thisQueryTags);
            $isActive = false !== $key;

            // building the href for the tag link such that:
            // a) clicking an active tag will make it not active on next page load
            // b) clicking a non-active tag will make it active on next page load

            if ($isActive) {
                array_splice($thisQueryTags, $key, 1);
            } else {
                $thisQueryTags[] = $slug;
            }

            unset($thisQuery['tags']);
            $queryString = http_build_query($thisQuery);
            $tagQueryString = [];

            foreach ($thisQueryTags as $slug) {
                $tagQueryString[] = 'tags[]='.urlencode($slug);
            }

            $tagQueryString = implode('&', $tagQueryString);

            if ($queryString) {
                $queryString = $queryString.'&'.$tagQueryString;
            } else {
                $queryString = $tagQueryString;
            }

            $href = $pageHref;

            if ($queryString) {
                $href .= '?'.$queryString;
            }

            $tagsArray[] = [
                'href' => $href,
                'name' => $tag->getName(),
                'active' => $isActive,
            ];
        }

        if ($tagsArray) {
            array_unshift($tagsArray, [
                'href' => $pageHref,
                'name' => 'All',
                'active' => empty($activeTags),
            ]);
        }

        return $tagsArray;
    }

    private function getSchema(Article $article): string
    {
        $pagePath = $this->pageRenderer->getCurrentPage()->getPath();

        $url = $this->urlGenerator->generate(
            'oh_media_page_frontend',
            ['path' => $pagePath.'/'.$article->getSlug()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $article->getTitle(),
            'description' => $article->getSnippet(),
            'datePublished' => $article->getPublishedAt()->format('c'),
            'url' => $url,
            'dateModified' => $article->getUpdatedAt()->format('c'),
        ];

        if ($author = $article->getAuthor()) {
            $schema['author'] = [
                '@type' => 'Person',
                'name' => $author,
            ];
        }

        $organizationName = $this->settings->get('schema_organization_name');

        if ($organizationName) {
            $schema['publisher'] = [
                '@type' => 'Organization',
                'name' => $organizationName,
            ];
        }

        $image = $article->getImage();

        if ($image) {
            $path = $this->fileManager->getWebPath($image);

            $schema['image'] = $this->urlHelper->getAbsoluteUrl($path);
        }

        return '<script type="application/ld+json">'.json_encode($schema).'</script>';
    }
}
