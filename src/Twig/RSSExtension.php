<?php

namespace OHMedia\NewsBundle\Twig;

use OHMedia\NewsBundle\Entity\Article;
use OHMedia\PageBundle\Service\PageRawQuery;
use OHMedia\SettingsBundle\Service\Settings;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class RSSExtension extends AbstractExtension
{
    public function __construct(
        private Settings $settings,
        private UrlGeneratorInterface $urlGenerator,
        private PageRawQuery $pageRawQuery,
        #[Autowire('%oh_media_news.page_template%')]
        private string $pageTemplate,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('news_rss_link_tag', [$this, 'rssLinkTag'], [
                'is_safe' => ['html'],
            ]),
        ];
    }

    public function rssLinkTag(): string
    {
        $pagePath = $this->pageRawQuery->getPathWithTemplate($this->pageTemplate);

        if (!$pagePath) {
            return '';
        }

        $title = (string) $this->settings->get(Article::SETTING_RSS_TITLE);
        $href = $this->urlGenerator->generate('news_rss');

        return sprintf(
            '<link rel="alternate" type="application/rss+xml" title="%s" href="%s">',
            htmlspecialchars($title),
            $href,
        );
    }
}
