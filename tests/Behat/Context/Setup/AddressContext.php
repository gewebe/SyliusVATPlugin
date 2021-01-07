<?php

declare(strict_types=1);

namespace Tests\Gewebe\SyliusVATPlugin\Behat\Context\Setup;

use Behat\Behat\Context\Context;
use Doctrine\Persistence\ObjectManager;
use Gewebe\SyliusVATPlugin\Entity\VatNumberAddressInterface;
use Sylius\Component\Core\Model\CustomerInterface;

class AddressContext implements Context
{
    /** @var ObjectManager */
    private $objectManager;

    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @Given /^(their) default address VAT number is "([^"]+)"$/
     * @Given /^(their) default address VAT number is "([^"]+)" (validated|invalidated) since "([^"]+)"/
     */
    public function theirDefaultAddressVatNumberIs(
        CustomerInterface $customer,
        $vatNumber,
        $validation = null,
        $validationDate = null
    ) {
        /** @var VatNumberAddressInterface $address */
        $address = $customer->getDefaultAddress();

        $address->setVatNumber($vatNumber);

        if ($validation) {
            $valid = $validation == 'validated' ? true : false;

            $address->setVatValid($valid, new \DateTime($validationDate));
        }

        $this->objectManager->flush();
    }
}
