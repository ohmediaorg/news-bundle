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
        if (!$this->isGranted(ArticleVoter::INDEX, new Article())) {
            return null;
        }

        if ($this->isGranted(ArticleTagVoter::INDEX, new ArticleTag())) {
            return (new NavDropdown('Articles', 'article_index'))
                    ->setIcon('newspaper')
                    ->addLink(new NavLink('Articles', 'article_index'))
                    ->addLink(new NavLink('Tags', 'article_tag_index'));
        } else {
            return (new NavLink('Articles', 'article_index'))
                ->setIcon('newspaper');
        }

        return null;
    }
}
