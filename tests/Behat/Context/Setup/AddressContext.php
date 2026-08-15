<?php

declare(strict_types=1);

namespace Tests\Gewebe\SyliusVATPlugin\Behat\Context\Setup;

use Behat\Behat\Context\Context;
use Behat\Step\Given;
use DateTime;
use Doctrine\Persistence\ObjectManager;
use Gewebe\SyliusVATPlugin\Entity\VatNumberAddressInterface;
use Sylius\Component\Core\Model\AddressInterface;
use Sylius\Component\Core\Model\CustomerInterface;

class AddressContext implements Context
{
    public function __construct(
        private readonly ObjectManager $objectManager,
    ) {
    }

    #[Given('/^(their) default address VAT number is "([^"]+)"(?: (validated|invalidated) since "([^"]+)")?$/')]
    public function theirDefaultAddressVatNumberIs(
        CustomerInterface $customer,
        string $vatNumber,
        ?string $validation = null,
        ?string $validationDate = null,
    ): void {
        /** @var VatNumberAddressInterface $address */
        $address = $customer->getDefaultAddress();

        $address->setVatNumber($vatNumber);

        if (null !== $validation) {
            $address->setVatValid($validation === 'validated', new DateTime($validationDate ?? 'now'));
        }

        $this->objectManager->flush();
    }

    #[Given('/^the ("[^"]+" street) VAT number is "([^"]+)"$/')]
    public function theAddressVatNumberIs(AddressInterface $address, string $vatNumber): void
    {
        if (!$address instanceof VatNumberAddressInterface) {
            throw new \InvalidArgumentException('The address does not support VAT numbers.');
        }

        $address->setVatNumber($vatNumber);
        $this->objectManager->flush();
    }
}
