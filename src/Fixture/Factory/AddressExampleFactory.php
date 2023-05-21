<?php

declare(strict_types=1);

namespace Gewebe\SyliusVATPlugin\Fixture\Factory;

use Gewebe\SyliusVATPlugin\Entity\VatNumberAddressInterface;
use Sylius\Bundle\CoreBundle\Fixture\Factory\AddressExampleFactory as BaseAddressExampleFactory;
use Sylius\Component\Core\Model\AddressInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class AddressExampleFactory extends BaseAddressExampleFactory
{
    public function create(array $options = []): AddressInterface
    {
        $address = parent::create($options);

        if (!$address instanceof VatNumberAddressInterface) {
            return $address;
        }

        if (isset($options['vat_number'])) {
            $address->setVatNumber((string) $options['vat_number']);
        }

        if (isset($options['vat_valid'])) {
            $address->setVatValid((bool) $options['vat_valid']);
        }

        return $address;
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver
            ->setDefault('vat_number', null)
            ->setAllowedTypes('vat_number', ['null', 'string'])
            ->setDefault('vat_valid', false)
            ->setAllowedTypes('vat_valid', 'bool')
            ->setDefault('vat_validated_at', null)
            ->setAllowedTypes('vat_validated_at', ['null', 'string'])
        ;
    }
}
