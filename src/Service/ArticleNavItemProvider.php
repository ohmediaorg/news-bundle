<?php

namespace App\Service\Backend\Nav;

use App\Entity\Article;
use App\Security\Voter\ArticleVoter;
use OHMedia\BackendBundle\Service\AbstractNavItemProvider;
use OHMedia\BootstrapBundle\Component\Nav\NavItemInterface;
use OHMedia\BootstrapBundle\Component\Nav\NavLink;

class ArticleNavItemProvider extends AbstractNavItemProvider
{
    public function getNavItem(): ?NavItemInterface
    {
        if ($this->isGranted(ArticleVoter::INDEX, new Article())) {
            return (new NavLink('Articles', 'article_index'))
                ->setIcon('newspaper');
        }

        return null;
    }
}
