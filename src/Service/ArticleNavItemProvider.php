<?php

namespace OHMedia\NewsBundle\Service;

use OHMedia\NewsBundle\Entity\Article;
use OHMedia\NewsBundle\Security\Voter\ArticleVoter;
use OHMedia\BackendBundle\Service\AbstractNavItemProvider;
use OHMedia\BootstrapBundle\Component\Nav\NavItemInterface;
use OHMedia\BootstrapBundle\Component\Nav\NavLink;
use OHMedia\BootstrapBundle\Component\Nav\NavDropdown;

class ArticleNavItemProvider extends AbstractNavItemProvider
{
    public function getNavItem(): ?NavItemInterface
    {
        if ($this->isGranted(ArticleVoter::INDEX, new Article())) {
            return (new NavDropdown('Articles', 'article_index'))
                ->setIcon('newspaper')
                ->addLink((new NavLink('Articles', 'article_index'))
                    ->setIcon('newspaper'))
                ->addLink((new NavLink('Tags', 'article_tag_index'))
                    ->setIcon('tags'));
        }

        return null;
    }
}
