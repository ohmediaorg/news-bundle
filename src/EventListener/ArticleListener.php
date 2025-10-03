<?php

namespace OHMedia\NewsBundle\EventListener;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PostLoadEventArgs;
use Doctrine\ORM\Events;
use OHMedia\NewsBundle\Entity\Article;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsEntityListener(event: Events::postLoad, method: 'postLoad', entity: Article::class)]
class ArticleListener
{
    public function __construct(
        #[Autowire('%oh_media_news.article_tags%')]
        private bool $articleTagsEnabled,
    ) {
    }

    public function postLoad(Article $article, PostLoadEventArgs $event)
    {
        if (!$this->articleTagsEnabled) {
            $article->clearTags();
        }
    }
}
