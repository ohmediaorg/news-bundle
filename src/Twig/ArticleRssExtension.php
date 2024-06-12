<?php

namespace OHMedia\NewsBundle\Twig;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ArticleRssExtension extends AbstractExtension
{
    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('article_rss', [$this, 'rss'], [
                'is_safe' => ['html'],
            ]),
        ];
    }

    public function rss(): string
    {
        // TODO get title
        $title = "TODO";
        $url = $this->urlGenerator->generate('news_rss', [], UrlGeneratorInterface::ABSOLUTE_URL);

        return sprintf(
            '<link rel="alternate" type="application/rss+xml" title="%s" href="%s">',
            $title,
            $url
        );
    }
}
