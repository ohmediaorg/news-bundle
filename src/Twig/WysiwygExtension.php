<?php

namespace OHMedia\NewsBundle\Twig;

use OHMedia\BootstrapBundle\Service\Paginator;
use OHMedia\NewsBundle\Entity\Article;
use OHMedia\NewsBundle\Repository\ArticleRepository;
use OHMedia\FileBundle\Service\FileManager;
use OHMedia\MetaBundle\Entity\Meta;
use OHMedia\PageBundle\Event\DynamicPageEvent;
use OHMedia\PageBundle\Service\PageRenderer;
use OHMedia\SettingsBundle\Service\Settings;
use OHMedia\WysiwygBundle\Twig\AbstractWysiwygExtension;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\UrlHelper;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Twig\Environment;
use Twig\TwigFunction;

#[AsEventListener(event: DynamicPageEvent::class, method: 'onDynamicPageEvent')]
class WysiwygExtension extends AbstractWysiwygExtension
{
    private bool $rendered = false;
    private ?Article $articleEntity = null;

    public function __construct(
        private ArticleRepository $articleRepository,
        private FileManager $fileManager,
        private PageRenderer $pageRenderer,
        private Paginator $paginator,
        private Settings $settings,
        private UrlHelper $urlHelper,
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

        if (!$pageRevision->containsShortcode('news()')) {
            return;
        }

        $dynamicPageEvent->stopPropagation();

        $dynamicPart = $this->pageRenderer->getDynamicPart();

        $qb = $this->articleRepository->getPublishedArticles();
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

        $qb = $this->articleRepository->getPublishedArticles();

        return $twig->render('@OHMediaNews/news_listing.html.twig', [
            'pagination' => $this->paginator->paginate($qb, 12),
            'news_page_path' => $pagePath,
        ]);
    }

    private function getSchema(Article $article): string
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'name' => $article->getTitle(),
            'description' => $article->getSnippet(),
            'datePublished' => $article->getPublishDatetime()->format('c'),
        ];

        if ($author = $article->getAuthor()) {
            $schema['author'] = $author;
        }

        $organizationName = $this->settings->get('schema_organization_name');

        if ($organizationName) {
            $schema['organizer'] = [
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
