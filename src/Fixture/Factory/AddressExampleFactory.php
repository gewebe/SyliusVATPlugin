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
        $options = $this->optionsResolver->resolve($options);

        $address = parent::create($options);

        if (!$address instanceof VatNumberAddressInterface) {
            return $address;
        }

        if (isset($options['vat_number'])) {
            $address->setVatNumber($options['vat_number']);
        }

        if (isset($options['vat_valid'])) {
            $address->setVatValid($options['vat_valid']);
        }

        return $address;
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver
            ->setDefault('vat_number', null)
            ->setAllowedTypes('vat_number', ['null', 'string'])
            ->setDefault('vat_valid', null)
            ->setAllowedTypes('vat_valid', ['null', 'bool'])
            ->setDefault('vat_validated_at', null)
            ->setAllowedTypes('vat_validated_at', ['null', 'string'])
        ;
    }
}
