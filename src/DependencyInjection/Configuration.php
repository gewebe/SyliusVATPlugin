<?php

declare(strict_types=1);

namespace Gewebe\SyliusVATPlugin\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    /**
     * @psalm-suppress MixedMethodCall
     * @psalm-suppress UndefinedMethod
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('gewebe_sylius_vat');

        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode('order')
                    ->children()
                        ->booleanNode('recalculate')->defaultValue(true)->end()
                    ->end()
                ->end() // order
                ->arrayNode('validate')
                    ->children()
                        ->booleanNode('is_active')->defaultValue(true)->end()
                        ->booleanNode('country')->defaultValue(true)->end()
                        ->booleanNode('existence')->defaultValue(true)->end()
                    ->end()
                ->end() // validation
                ->arrayNode('required')
                    ->children()
                        ->booleanNode('default')->defaultValue(true)->end()
                        ->booleanNode('company')->defaultValue(true)->end()
                        ->arrayNode('countries')
                            ->scalarPrototype()->end()
                        ->end()
                    ->end()
                ->end() // required
                ->arrayNode('revalidate')
                    ->children()
                        ->booleanNode('on_login')->defaultValue(true)->end()
                        ->integerNode('expiration_days')->defaultValue(30)->end()
                    ->end()
                ->end() // revalidate
        ;

        return $treeBuilder;
    }
}
