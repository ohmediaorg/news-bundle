<?php

namespace OHMedia\NewsBundle\Security\Voter;

use OHMedia\NewsBundle\Entity\Article;
use OHMedia\SecurityBundle\Entity\User;
use OHMedia\SecurityBundle\Security\Voter\AbstractEntityVoter;

class ArticleVoter extends AbstractEntityVoter
{
    public const INDEX = 'index';
    public const CREATE = 'create';
    public const VIEW = 'view';
    public const EDIT = 'edit';
    public const DELETE = 'delete';
    public const SETTINGS = 'settings';

    protected function getAttributes(): array
    {
        return [
            self::INDEX,
            self::CREATE,
            self::VIEW,
            self::EDIT,
            self::DELETE,
            self::SETTINGS,
        ];
    }

    protected function getEntityClass(): string
    {
        return Article::class;
    }

    protected function canIndex(Article $article, User $loggedIn): bool
    {
        return true;
    }

    protected function canCreate(Article $article, User $loggedIn): bool
    {
        return true;
    }

    protected function canView(Article $article, User $loggedIn): bool
    {
        return true;
    }

    protected function canEdit(Article $article, User $loggedIn): bool
    {
        return true;
    }

    protected function canDelete(Article $article, User $loggedIn): bool
    {
        return true;
    }

    protected function canSettings(Article $article, User $loggedIn): bool
    {
        return true;
    }
}
