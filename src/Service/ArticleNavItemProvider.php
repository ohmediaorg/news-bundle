<?php

namespace OHMedia\NewsBundle\Service;

use OHMedia\BackendBundle\Service\AbstractNavItemProvider;
use OHMedia\BootstrapBundle\Component\Nav\NavDropdown;
use OHMedia\BootstrapBundle\Component\Nav\NavItemInterface;
use OHMedia\BootstrapBundle\Component\Nav\NavLink;
use OHMedia\NewsBundle\Entity\Article;
use OHMedia\NewsBundle\Entity\ArticleTag;
use OHMedia\NewsBundle\Security\Voter\ArticleTagVoter;
use OHMedia\NewsBundle\Security\Voter\ArticleVoter;

class ArticleNavItemProvider extends AbstractNavItemProvider
{
    public function getNavItem(): ?NavItemInterface
    {
        $nav = null;

        if ($this->isGranted(ArticleVoter::INDEX, new Article())) {
            $nav = (new NavDropdown('Articles', 'article_index'))
                ->setIcon('newspaper')
                ->addLink((new NavLink('Articles', 'article_index'))
                    ->setIcon('newspaper'));

            if ($this->isGranted(ArticleTagVoter::INDEX, new ArticleTag())) {
                $nav->addLink((new NavLink('Tags', 'article_tag_index'))
                    ->setIcon('tags'));
            }
        }

        return $nav;
    }
}
