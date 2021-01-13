<?php

declare(strict_types=1);

namespace Gewebe\SyliusVATPlugin\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    /**
     * @psalm-suppress MixedMethodCall
     * @psalm-suppress PossiblyUndefinedMethod
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
                        ->booleanNode('format')->defaultValue(true)->end()
                        ->booleanNode('country')->defaultValue(true)->end()
                        ->booleanNode('existence')->defaultValue(true)->end()
                    ->end()
                ->end() // validation
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
