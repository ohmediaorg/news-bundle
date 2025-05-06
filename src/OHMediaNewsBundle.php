<?php

namespace OHMedia\NewsBundle;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class OHMediaNewsBundle extends AbstractBundle
{
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
            ->children()
                ->booleanNode('article_tags')
                    ->defaultTrue()
                ->end()
                ->scalarNode('page_template')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
            ->end();
    }

    public function loadExtension(
        array $config,
        ContainerConfigurator $containerConfigurator,
        ContainerBuilder $containerBuilder
    ): void {
        $containerConfigurator->import('../config/services.yaml');

        $containerConfigurator->parameters()
            ->set('oh_media_news.article_tags', $config['article_tags'])
            ->set('oh_media_news.page_template', $config['page_template'])
        ;
    }
}
