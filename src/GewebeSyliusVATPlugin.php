<?php

declare(strict_types=1);

namespace Gewebe\SyliusVATPlugin;

use Gewebe\SyliusVATPlugin\DependencyInjection\Compiler\ConfigureCheckoutAddressFormComponentPass;
use Sylius\Bundle\CoreBundle\Application\SyliusPluginTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class GewebeSyliusVATPlugin extends Bundle
{
    use SyliusPluginTrait;

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        // Sylius 2.1 does not provide address form value modifiers. The compiler pass replaces the checkout
        // address component with a compatibility fallback after all bundle services have been loaded.
        // TODO: Remove the compiler pass and the fallback component when support for Sylius 2.1 is dropped.
        $container->addCompilerPass(new ConfigureCheckoutAddressFormComponentPass());
    }

    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
