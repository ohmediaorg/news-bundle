<?php

namespace OHMedia\NewsBundle\Service;

use OHMedia\NewsBundle\Entity\ArticleTag;
use OHMedia\SecurityBundle\Service\EntityChoiceInterface;

class ArticleTagEntityChoice implements EntityChoiceInterface
{
    public function getLabel(): string
    {
        return 'Article Tags';
    }

    public function getEntities(): array
    {
        return [
            ArticleTag::class,
        ];
    }
}
