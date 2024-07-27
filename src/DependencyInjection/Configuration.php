<?php
// src/DependencyInjection/Configuration.php

namespace Pbdkn\ContaoBesslichschmuck\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('pbdkn_contao_besslichschmuck');

        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->scalarNode('some_parameter')->defaultValue('default_value')->end()
                ->arrayNode('some_array')
                    ->children()
                        ->scalarNode('key1')->end()
                        ->scalarNode('key2')->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
