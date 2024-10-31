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
use OHMedia\TimezoneBundle\Service\Timezone;
use OHMedia\WysiwygBundle\Twig\AbstractWysiwygExtension;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\UrlHelper;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;
use Twig\TwigFunction;

#[AsEventListener(event: DynamicPageEvent::class, method: 'onDynamicPageEvent')]
class WysiwygExtension extends AbstractWysiwygExtension
{
    private bool $rendered = false;
    private ?Article $articleEntity = null;
    private $timezone;

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
        private bool $enabledArticleTags,
        private RequestStack $requestStack,
        Timezone $timezoneService,
    ) {
        $this->timezone = new \DateTimeZone($timezoneService->get());
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

        $callable = $pageRevision->getTemplate().'::getTemplate';
        $isTemplate = is_callable($callable)
            ? '@OHMediaNews/news.html.twig' === call_user_func($callable)
            : false;
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

    public function news(
        Environment $twig,
    ): string {
        if ($this->rendered) {
            return '';
        }

        $this->rendered = true;

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

        if ($this->enabledArticleTags) {
            $tags = $this->articleTagRepository->createQueryBuilder('at')
                ->select('at')
                ->innerJoin('at.articles', 'a')
                ->getQuery()
                ->getResult();

            // accommodates multiple tags (ie. `tags[]=abc&tags[]=123`)
            $activeTags = isset($query['tags']) && is_array($query['tags']) ?
                $query['tags'] :
                [];

            $tagsArray = [];

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

                if ($thisQueryTags) {
                    $thisQuery['tags'] = $thisQueryTags;
                } else {
                    unset($thisQuery['tags']);
                }

                $href = $pagePath;

                if ($thisQuery) {
                    $href .= '?'.http_build_query($thisQuery);
                    $href = str_replace(['%5B', '%5D'], ['[', ']'], $href);
                }

                $tagsArray[] = [
                    'href' => $href,
                    'name' => $tag->getName(),
                    'active' => $isActive,
                ];
            }

            if ($activeTags) {
                $qb->innerJoin('a.tags', 't');
                $qb->andWhere('t.slug IN (:tags)');
                $qb->setParameter('tags', $activeTags);
            }
        }

        $pagination = $this->paginator->paginate($qb, 12);
        $articles = $pagination->getResults();

        foreach ($articles as $article) {
            $article->setTimezone($this->timezone);
        }

        return $twig->render('@OHMediaNews/news_listing.html.twig', [
            'pagination' => $pagination,
            'news_page_path' => $pagePath,
            'tags' => $tagsArray,
        ]);
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
            'datePublished' => $article->getPublishDatetime()->format('c'),
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
