<?php

declare(strict_types=1);

namespace Gweb\SyliusVATPlugin\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

final class GwebSyliusVATExtension extends Extension
{
    /** {@inheritdoc} */
    public function load(array $config, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');

        $config = $this->processConfiguration($this->getConfiguration([], $container), $config);

        $definition = $container->getDefinition('gweb_sylius_vat.order_processor');
        $definition->replaceArgument(0, $config['order']['recalculate']);

        $definition = $container->getDefinition('gweb_sylius_vat.validator');
        $definition->replaceArgument(1, $config['validate']['format']);
        $definition->replaceArgument(2, $config['validate']['country']);
        $definition->replaceArgument(3, $config['validate']['existence']);
    }
}
