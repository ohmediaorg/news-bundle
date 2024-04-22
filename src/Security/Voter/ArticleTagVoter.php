<?php

namespace OHMedia\NewsBundle\Security\Voter;

use OHMedia\NewsBundle\Entity\ArticleTag;
use OHMedia\SecurityBundle\Entity\User;
use OHMedia\SecurityBundle\Security\Voter\AbstractEntityVoter;

class ArticleTagVoter extends AbstractEntityVoter
{
    public const INDEX = 'index';
    public const CREATE = 'create';
    public const VIEW = 'view';
    public const EDIT = 'edit';
    public const DELETE = 'delete';

    protected function getAttributes(): array
    {
        return [
            self::INDEX,
            self::CREATE,
            self::VIEW,
            self::EDIT,
            self::DELETE,
        ];
    }

    protected function getEntityClass(): string
    {
        return ArticleTag::class;
    }

    protected function canIndex(ArticleTag $articleTag, User $loggedIn): bool
    {
        return true;
    }

    protected function canCreate(ArticleTag $articleTag, User $loggedIn): bool
    {
        return true;
    }

    protected function canView(ArticleTag $articleTag, User $loggedIn): bool
    {
        return true;
    }

    protected function canEdit(ArticleTag $articleTag, User $loggedIn): bool
    {
        return true;
    }

    protected function canDelete(ArticleTag $articleTag, User $loggedIn): bool
    {
        return true;
    }
}
