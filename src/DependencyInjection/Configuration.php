<?php

declare(strict_types=1);

namespace Gewebe\SyliusVATPlugin\DependencyInjection;

use Gewebe\SyliusVATPlugin\Vat\Number\Hmrc\HmrcClient;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('gewebe_sylius_vat');

        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode('order')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('recalculate')->defaultValue(true)
                            ->info('Order will be recalculated without taxes if possible.')
                        ->end()
                    ->end()
                ->end() // order
                ->arrayNode('required')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('default')->defaultValue(false)
                            ->info('VAT number is required by default.')
                        ->end()
                        ->booleanNode('company')->defaultValue(true)
                            ->info('VAT number is required for a company address.')
                        ->end()
                        ->arrayNode('countries')
                            ->scalarPrototype()->end()
                            ->info('VAT number is required for the specified countries.')
                        ->end()
                    ->end()
                ->end() // required
                ->arrayNode('validate')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('format')->defaultValue(true)
                            ->info('Verify the country-specific VAT number format.')
                        ->end()
                        ->booleanNode('country')->defaultValue(true)
                            ->info('Verify that the VAT number matches the selected country.')
                        ->end()
                        ->booleanNode('registration')->defaultValue(true)
                            ->info('Verify that the VAT number is successfully registered online.')
                        ->end()
                        ->booleanNode('on_service_unavailable')->defaultValue(false)
                            ->info('Validation will not fail if the VAT service is unavailable, '
                                        . 'but the VAT number is not verified, which can be tried again later.')
                        ->end()
                    ->end()
                ->end() // validate
                ->arrayNode('hmrc')
                    ->addDefaultsIfNotSet()
                    ->info('Online validation of UK VAT numbers through the HMRC API.')
                    ->children()
                        ->scalarNode('base_url')->defaultValue(HmrcClient::PRODUCTION_BASE_URL)
                            ->cannotBeEmpty()
                            ->info('Base url of the HMRC API, use ' . HmrcClient::SANDBOX_BASE_URL . ' for testing.')
                        ->end()
                        ->scalarNode('client_id')->defaultNull()
                            ->info('Client ID of the HMRC API')
                        ->end()
                        ->scalarNode('client_secret')->defaultNull()
                            ->info('Client secret of the HMRC API')
                        ->end()
                    ->end()
                ->end() // hmrc
                ->arrayNode('revalidate')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('on_login')->defaultValue(true)
                            ->info('VAT number will be revalidated again on login.')
                        ->end()
                        ->integerNode('expiration_days')->defaultValue(30)
                            ->info('Number of days after which the VAT number will be revalidated.')
                        ->end()
                    ->end()
                ->end() // revalidate
        ;

        return $treeBuilder;
    }
}
