<?php

namespace App\Service\Backend\Nav;

use App\Entity\ArticleTag;
use App\Security\Voter\ArticleTagVoter;
use OHMedia\BackendBundle\Service\AbstractNavItemProvider;
use OHMedia\BootstrapBundle\Component\Nav\NavItemInterface;
use OHMedia\BootstrapBundle\Component\Nav\NavLink;

class ArticleTagNavItemProvider extends AbstractNavItemProvider
{
    public function getNavItem(): ?NavItemInterface
    {
        if ($this->isGranted(ArticleTagVoter::INDEX, new ArticleTag())) {
            return (new NavLink('Article Tags', 'article_tag_index'))
                ->setIcon('tag');
        }

        return null;
    }
}
