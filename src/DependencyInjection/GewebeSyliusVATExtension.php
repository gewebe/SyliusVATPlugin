<?php

declare(strict_types=1);

namespace Gewebe\SyliusVATPlugin\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

final class GewebeSyliusVATExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../../config'));
        $loader->load('services.yml');

        $configuration = $this->getConfiguration([], $container);
        if ($configuration === null) {
            return;
        }

        /** @var string[][] $configs */
        $configs = $this->processConfiguration($configuration, $configs);

        $definition = $container->getDefinition('gewebe_sylius_vat_plugin.order_processor');
        $definition->replaceArgument(1, $configs['order']['recalculate']);

        $definition = $container->getDefinition('gewebe_sylius_vat_plugin.validator');
        $definition->replaceArgument(1, $configs['validate']['is_active']);
        $definition->replaceArgument(2, $configs['validate']['country']);
        $definition->replaceArgument(3, $configs['validate']['existence']);

        $definition = $container->getDefinition('Gewebe\SyliusVATPlugin\EventListener\LoginListener');
        $definition->replaceArgument(1, $configs['revalidate']['on_login']);
        $definition->replaceArgument(2, $configs['revalidate']['expiration_days']);
    }
}
