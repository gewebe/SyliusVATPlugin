<?php

declare(strict_types=1);

namespace Gewebe\SyliusVATPlugin\Form\Modifier;

use Gewebe\SyliusVATPlugin\Entity\VatNumberAddressInterface;
use Sylius\Bundle\ShopBundle\Modifier\AddressFormValuesModifierInterface;
use Sylius\Component\Addressing\Model\AddressInterface;

final readonly class VatNumberAddressFormValuesModifier implements AddressFormValuesModifierInterface
{
    /**
     * @param array<string, mixed> $addressData
     *
     * @return array<string, mixed>
     */
    public function modify(array $addressData, AddressInterface $address): array
    {
        if ($address instanceof VatNumberAddressInterface) {
            $addressData['vatNumber'] = $address->getVatNumber();
        }

        return $addressData;
    }
}
