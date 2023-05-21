<?php

declare(strict_types=1);

namespace Tests\Gewebe\SyliusVATPlugin\Behat\Context\Ui;

use Behat\Behat\Context\Context;
use Doctrine\Persistence\ObjectManager;
use Gewebe\SyliusVATPlugin\Entity\VatNumberAddressInterface;
use Sylius\Behat\Service\SharedStorageInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Webmozart\Assert\Assert;

class VatContext implements Context
{
    public function __construct(
        private ObjectManager $objectManager,
        private SharedStorageInterface $sharedStorage,
    ) {
    }

    /**
     * @Then my VAT number for the default address was :validation :validationDate
     */
    public function vatNumberHasJustBeenValidated($validation, $validationDate)
    {
        /** @var CustomerInterface $customer */
        $customer = $this->sharedStorage->get('customer');

        /** @var VatNumberAddressInterface $address */
        $address = $customer->getDefaultAddress();

        $this->objectManager->refresh($address);

        if ($validation == 'validated') {
            Assert::true($address->hasValidVatNumber());
        } elseif ($validation == 'invalidated') {
            Assert::false($address->hasValidVatNumber());
        }

        Assert::true($address->getVatValidatedAt()->diff(new \DateTime($validationDate))->d == 0);
    }
}
