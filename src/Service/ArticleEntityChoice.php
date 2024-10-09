<?php

namespace OHMedia\NewsBundle\Service;

use OHMedia\NewsBundle\Entity\Article;
use OHMedia\NewsBundle\Entity\ArticleTag;
use OHMedia\SecurityBundle\Service\EntityChoiceInterface;

class ArticleEntityChoice implements EntityChoiceInterface
{
    public function getLabel(): string
    {
        return 'Articles';
    }

    // TODO - might want seperate permissions
    public function getEntities(): array
    {
        return [
            Article::class,
            ArticleTag::class,
        ];
    }
}
