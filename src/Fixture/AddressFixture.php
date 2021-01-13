<?php

declare(strict_types=1);

namespace Gewebe\SyliusVATPlugin\Fixture;

use Sylius\Bundle\CoreBundle\Fixture\AddressFixture as BaseAddressFixture;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

final class AddressFixture extends BaseAddressFixture
{
    protected function configureResourceNode(ArrayNodeDefinition $resourceNode): void
    {
        parent::configureResourceNode($resourceNode);

        $node = $resourceNode->children();
        $node->scalarNode('vat_number');
        $node->booleanNode('vat_valid')->defaultFalse();
        $node->scalarNode('vat_validated_at');
    }
}
