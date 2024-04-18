<?php

namespace App\Service\EntityChoice;

use App\Entity\ArticleTag;
use OHMedia\SecurityBundle\Service\EntityChoiceInterface;

class ArticleTagEntityChoice implements EntityChoiceInterface
{
    public function getLabel(): string
    {
        return 'Article Tags';
    }

    public function getEntities(): array
    {
        return [ArticleTag::class];
    }
}
