<?php

namespace App\Service\EntityChoice;

use App\Entity\Article;
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
