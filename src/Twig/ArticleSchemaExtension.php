<?php

namespace OHMedia\NewsBundle\Twig;

use OHMedia\NewsBundle\Entity\Article;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ArticleSchemaExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('article_schema', [$this, 'schema'], [
                'is_safe' => ['html'],
            ]),
        ];
    }

    public function schema(Article $article, string $webRoot): array
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $article->getTitle(),
            'datePublished' => $article->getPublishDatetime()->format(\DateTime::ATOM),
            'dateModified' => $article->getUpdatedAt()->format(\DateTime::ATOM),
            'image' => [],
            'text' => $this->cleanStrings($article->getContent()), // TODO can I use the snippet instead?
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

        return $schema;
    }

    private function cleanStrings(string $string): string
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
}
