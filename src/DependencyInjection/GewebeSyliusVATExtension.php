<?php

declare(strict_types=1);

namespace Gewebe\SyliusVATPlugin\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

final class GewebeSyliusVATExtension extends Extension
{
    /** {@inheritdoc} */
    public function load(array $config, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');

        $config = $this->processConfiguration($this->getConfiguration([], $container), $config);

        $definition = $container->getDefinition('gewebe_sylius_vat_plugin.order_processor');
        $definition->replaceArgument(0, $config['order']['recalculate']);

        $definition = $container->getDefinition('gewebe_sylius_vat_plugin.validator');
        $definition->replaceArgument(1, $config['validate']['format']);
        $definition->replaceArgument(2, $config['validate']['country']);
        $definition->replaceArgument(3, $config['validate']['existence']);

        $definition = $container->getDefinition('Gewebe\SyliusVATPlugin\EventListener\LoginListener');
        $definition->replaceArgument(1, $config['revalidate']['on_login']);
        $definition->replaceArgument(2, $config['revalidate']['expiration_days']);
    }
}
