<?php

namespace Biig\Component\Elasticsearch\Integration\Symfony\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('biig_elasticsearch');

        if (method_exists($treeBuilder, 'getRootNode')) {
            $rootNode = $treeBuilder->getRootNode();
        } else {
            // BC for symfony/config < 4.2
            $rootNode = $treeBuilder->root('biig_elasticsearch');
        }

        $rootNode
            ->children()
                ->arrayNode('connections')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('port')->defaultValue(9200)->end()
                            ->scalarNode('host')->defaultValue('127.0.0.1')->end()
                            ->scalarNode('path')->defaultNull()->end()
                            ->scalarNode('url')->defaultNull()->end()
                            ->scalarNode('proxy')->defaultNull()->end()
                            ->scalarNode('transport')->defaultNull()->end()
                            ->scalarNode('persistent')->defaultNull()->end()
                            ->scalarNode('timeout')->defaultNull()->end()
                            ->scalarNode('connections')->defaultValue([])->end()
                            ->scalarNode('roundRobin')->defaultFalse()->end()
                            ->scalarNode('log')->defaultFalse()->end()
                            ->scalarNode('retryOnConflict')->defaultValue(0)->end()
                            ->scalarNode('bigintConversion')->defaultFalse()->end()
                            ->scalarNode('username')->defaultNull()->end()
                            ->scalarNode('password')->defaultNull()->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('mapping')
                    ->defaultValue([])
                    ->scalarPrototype()->end()
                ->end()
                ->scalarNode('serializer')->defaultValue('serializer')->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
