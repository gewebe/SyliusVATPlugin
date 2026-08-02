<?php

declare(strict_types=1);

namespace Tests\Gewebe\SyliusVATPlugin\Behat\Context\Ui\Shop;

use Behat\Behat\Context\Context;
use Behat\Step\Then;
use Behat\Step\When;
use Doctrine\Persistence\ObjectManager;
use Gewebe\SyliusVATPlugin\Entity\VatNumberAddressInterface;
use Sylius\Component\Core\Model\AddressInterface;
use Sylius\Resource\Doctrine\Persistence\RepositoryInterface;
use Tests\Gewebe\SyliusVATPlugin\Behat\Page\Shop\Account\AddressBook\CreatePageInterface;
use Webmozart\Assert\Assert;

final class AddressBookVatContext implements Context
{
    public function __construct(
        private readonly CreatePageInterface $createPage,
        private readonly ObjectManager $objectManager,
        private readonly RepositoryInterface $addressRepository,
    ) {
    }

    #[When('/^I specify my company as "([^"]+)"$/')]
    public function iSpecifyMyVCompany(string $company): void
    {
        $this->createPage->specifyCompany($company);
    }

    #[When('/^I specify my VAT number as "([^"]+)"$/')]
    public function iSpecifyMyVatNumber(string $vatNumber): void
    {
        $this->createPage->specifyVatNumber($vatNumber);
    }

    #[Then('I should be notified that the VAT number is not valid')]
    public function iShouldBeNotifiedThatTheVatNumberIsNotValid(): void
    {
        Assert::true($this->createPage->hasVatNumberValidationMessage());
    }

    #[Then('I see in the database that vat number for :fullName is valid')]
    public function iSeeInTheDatabaseThatVatNumberForIsValid(string $fullName): void
    {
        $this->assertVatNumberValidStatus($fullName, true);
    }

    #[Then('I see in the database that vat number for :fullName is not valid')]
    public function iSeeInTheDatabaseThatVatNumberForIsNotValid(string $fullName): void
    {
        $this->assertVatNumberValidStatus($fullName, false);
    }

    private function assertVatNumberValidStatus(string $fullName, bool $shouldBeValid): void
    {
        [$firstName, $lastName] = explode(' ', $fullName);

        /** @var AddressInterface|VatNumberAddressInterface|null $address */
        $address = $this->addressRepository->findOneBy(['firstName' => $firstName, 'lastName' => $lastName]);

        Assert::notNull($address, sprintf('Address for "%s" not found.', $fullName));
        Assert::isInstanceOf($address, VatNumberAddressInterface::class);

        $this->objectManager->refresh($address);

        if ($shouldBeValid) {
            Assert::true($address->hasValidVatNumber(), 'VAT number should be valid in database but it is not.');
        } else {
            Assert::false($address->hasValidVatNumber(), 'VAT number should not be valid in database but it is.');
        }
    }
}
