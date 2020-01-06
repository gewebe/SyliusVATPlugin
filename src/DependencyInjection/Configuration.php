<?php

declare(strict_types=1);

namespace Gweb\SyliusVATPlugin\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    /** {@inheritdoc} */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('gweb_sylius_vat_number');

        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode('order')
                    ->children()
                        ->booleanNode('recalculate')->defaultValue(true)->end()
                    ->end()
                ->end() // order
                ->arrayNode('validate')
                    ->children()
                        ->booleanNode('format')->defaultValue(true)->end()
                        ->booleanNode('country')->defaultValue(true)->end()
                        ->booleanNode('existence')->defaultValue(true)->end()
                    ->end()
                ->end() // validation
        ;

        return $treeBuilder;
    }
}
