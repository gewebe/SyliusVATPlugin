<?php

declare(strict_types=1);

namespace Gewebe\SyliusVATPlugin\DependencyInjection\Compiler;

use Gewebe\SyliusVATPlugin\Twig\Component\Checkout\Address\FormComponent;
use Sylius\Bundle\ShopBundle\Modifier\AddressFormValuesModifierInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class ConfigureCheckoutAddressFormComponentPass implements CompilerPassInterface
{
    private const FORM_COMPONENT_SERVICE_ID = 'sylius_shop.twig.component.checkout.address.form';

    public function process(ContainerBuilder $container): void
    {
        if (
            interface_exists(AddressFormValuesModifierInterface::class) ||
            !$container->hasDefinition(self::FORM_COMPONENT_SERVICE_ID)
        ) {
            return;
        }

        $container->getDefinition(self::FORM_COMPONENT_SERVICE_ID)->setClass(FormComponent::class);
    }
}
