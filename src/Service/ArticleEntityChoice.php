<?php

namespace OHMedia\NewsBundle\Service\EntityChoice;

use OHMedia\NewsBundle\Entity\Article;
use OHMedia\SecurityBundle\Service\EntityChoiceInterface;

class ArticleEntityChoice implements EntityChoiceInterface
{
    public function getLabel(): string
    {
        return 'Articles';
    }

    public function getEntities(): array
    {
        return [Article::class];
    }
}
