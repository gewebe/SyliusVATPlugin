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

        /** @var string[][] $configs */
        $configs = $this->processConfiguration($configuration, $configs);

        $definition = $container->getDefinition('gewebe_sylius_vat_plugin.order_processor');
        $definition->replaceArgument(3, $configs['order']['recalculate']);

        $definition = $container->getDefinition('gewebe_sylius_vat_plugin.validator');
        $definition->replaceArgument(1, $configs['validate']['is_active']);
        $definition->replaceArgument(2, $configs['validate']['country']);
        $definition->replaceArgument(3, $configs['validate']['registration']);
        $definition->replaceArgument(4, $configs['required']['company']);
        $definition->replaceArgument(5, $configs['required']['countries']);

        $definition = $container->getDefinition('gewebe_sylius_vat_plugin.form.extension.address');
        $definition->replaceArgument(0, $configs['required']['default']);

        $definition = $container->getDefinition('Gewebe\SyliusVATPlugin\EventListener\LoginListener');
        $definition->replaceArgument(1, $configs['revalidate']['on_login']);
        $definition->replaceArgument(2, $configs['revalidate']['expiration_days']);
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
