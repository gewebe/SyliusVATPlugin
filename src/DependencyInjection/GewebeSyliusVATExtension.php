<?php

declare(strict_types=1);

namespace Gewebe\SyliusVATPlugin\DependencyInjection;

use Sylius\Bundle\CoreBundle\DependencyInjection\PrependDoctrineMigrationsTrait;
use Sylius\Bundle\ResourceBundle\DependencyInjection\Extension\AbstractResourceExtension;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

final class GewebeSyliusVATExtension extends AbstractResourceExtension implements PrependExtensionInterface
{
    use PrependDoctrineMigrationsTrait;

    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../../config'));
        $loader->load('services.yaml');

        $configuration = $this->getConfiguration([], $container);
        if ($configuration === null) {
            return;
        }

        $config = $this->processConfiguration($configuration, $configs);

        $definition = $container->getDefinition('gewebe_sylius_vat_plugin.order_processor');
        $definition->replaceArgument(3, $config['order']['recalculate']);

        $definition = $container->getDefinition('gewebe_sylius_vat_plugin.vat_number_validator_config');
        $definition->replaceArgument(0, $config['required']['default']);
        $definition->replaceArgument(1, $config['required']['company']);
        $definition->replaceArgument(2, $config['required']['countries']);
        $definition->replaceArgument(3, $config['validate']['format']);
        $definition->replaceArgument(4, $config['validate']['country']);
        $definition->replaceArgument(5, $config['validate']['registration']);
        $definition->replaceArgument(6, $config['validate']['on_service_unavailable']);
        $definition->replaceArgument(7, $config['revalidate']['on_login']);
        $definition->replaceArgument(8, $config['revalidate']['expiration_days']);
    }

    public function prepend(ContainerBuilder $container): void
    {
        $this->prependDoctrineMigrations($container);
    }

    protected function getMigrationsNamespace(): string
    {
        return 'Gewebe\SyliusVATPlugin\Migrations';
    }

    protected function getMigrationsDirectory(): string
    {
        return '@GewebeSyliusVATPlugin/src/Migrations';
    }

    protected function getNamespacesOfMigrationsExecutedBefore(): array
    {
        return [
            'Sylius\Bundle\CoreBundle\Migrations',
        ];
    }
}
