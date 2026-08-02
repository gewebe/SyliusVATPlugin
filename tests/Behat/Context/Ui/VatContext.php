<?php

declare(strict_types=1);

namespace Tests\Gewebe\SyliusVATPlugin\Behat\Context\Ui;

use Behat\Behat\Context\Context;
use Behat\Step\Then;
use DateTime;
use Doctrine\Persistence\ObjectManager;
use Gewebe\SyliusVATPlugin\Entity\VatNumberAddressInterface;
use Sylius\Behat\Service\SharedStorageInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Webmozart\Assert\Assert;

class VatContext implements Context
{
    public function __construct(
        private readonly ObjectManager $objectManager,
        private readonly SharedStorageInterface $sharedStorage,
    ) {
    }

    #[Then('my VAT number for the default address was :validation :validationDate')]
    public function vatNumberHasJustBeenValidated(string $validation, string $validationDate): void
    {
        /** @var CustomerInterface $customer */
        $customer = $this->sharedStorage->get('customer');

        /** @var VatNumberAddressInterface $address */
        $address = $customer->getDefaultAddress();

        $this->objectManager->refresh($address);

        if ($validation === 'validated') {
            Assert::true($address->hasValidVatNumber());
        } elseif ($validation === 'invalidated') {
            Assert::false($address->hasValidVatNumber());
        }

        $validatedAt = $address->getVatValidatedAt();
        Assert::notNull($validatedAt);
        Assert::same($validatedAt->diff(new DateTime($validationDate))->d, 0);
    }
}
