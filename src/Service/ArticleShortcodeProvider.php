<?php

namespace OHMedia\NewsBundle\Service;

use OHMedia\WysiwygBundle\Shortcodes\AbstractShortcodeProvider;
use OHMedia\WysiwygBundle\Shortcodes\Shortcode;

class ArticleShortcodeProvider extends AbstractShortcodeProvider
{
    public function getTitle(): string
    {
        return 'News';
    }

    public function buildShortcodes(): void
    {
        $this->addShortcode(new Shortcode('News Listing', 'news()', true));
    }
}
