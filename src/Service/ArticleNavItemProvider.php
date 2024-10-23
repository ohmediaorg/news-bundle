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
        $nav = (new NavDropdown('News'))
            ->setIcon('newspaper');

        if ($this->isGranted(ArticleVoter::INDEX, new Article())) {
            $articles = new NavLink('Articles', 'article_index');
            $articles->setIcon('newspaper');

            $nav->addLink($articles);
        }

        if ($this->isGranted(ArticleTagVoter::INDEX, new ArticleTag())) {
            $tags = new NavLink('Tags', 'article_tag_index');
            $tags->setIcon('tag');

            $nav->addLink($tags);
        }

        if (!$this->isGranted(ArticleVoter::SETTINGS, new Article())) {
            $settings = new NavLink('Settings', 'article_rss_settings');
            $settings->setIcon('gear-fill');

            $nav->addLink($settings);
        }

        return $nav;
    }
}
