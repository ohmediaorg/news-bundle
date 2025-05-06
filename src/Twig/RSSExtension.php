<?php

namespace OHMedia\NewsBundle\Twig;

use OHMedia\NewsBundle\Entity\Article;
use OHMedia\SettingsBundle\Service\Settings;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class RSSExtension extends AbstractExtension
{
    public function __construct(
        private Settings $settings,
        private UrlGeneratorInterface $urlGenerator,
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
        $title = (string) $this->settings->get(Article::SETTING_RSS_TITLE);
        $href = $this->urlGenerator->generate('news_rss');

        return sprintf(
            '<link rel="alternate" type="application/rss+xml" title="%s" href="%s">',
            htmlspecialchars($title),
            $href,
        );
    }
}
