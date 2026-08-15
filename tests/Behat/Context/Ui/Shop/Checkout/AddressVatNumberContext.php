<?php

declare(strict_types=1);

namespace Tests\Gewebe\SyliusVATPlugin\Behat\Context\Ui\Shop\Checkout;

use Behat\Behat\Context\Context;
use Behat\Step\Then;
use Behat\Step\When;
use Tests\Gewebe\SyliusVATPlugin\Behat\Page\Shop\Checkout\AddressPage;
use Webmozart\Assert\Assert;

final class AddressVatNumberContext implements Context
{
    public function __construct(private readonly AddressPage $addressPage)
    {
    }

    #[When('/^I specify the billing company as "([^"]+)"$/')]
    public function iSpecifyTheCompanyForBillingAddress(string $company): void
    {
        $this->addressPage->specifyBillingAddressCompany($company);
    }

    #[When('/^I specify the billing VAT number as "([^"]+)"$/')]
    public function iSpecifyTheVatNumberForBillingAddress(string $vatNumber): void
    {
        $this->addressPage->specifyBillingAddressVatNumber($vatNumber);
    }

    #[When('/^I specify the shipping company as "([^"]+)"$/')]
    public function iSpecifyTheCompanyForShippingAddress(string $company): void
    {
        $this->addressPage->specifyShippingAddressCompany($company);
    }

    #[When('/^I specify the shipping VAT number as "([^"]+)"$/')]
    public function iSpecifyTheVatNumberForShippingAddress(string $vatNumber): void
    {
        $this->addressPage->specifyShippingAddressVatNumber($vatNumber);
    }

    #[Then('/^the (shipping|billing) VAT number should be "([^"]+)"$/')]
    public function theVatNumberShouldBe(string $type, string $vatNumber): void
    {
        $actualVatNumber = $type === 'shipping'
            ? $this->addressPage->getShippingAddressVatNumber()
            : $this->addressPage->getBillingAddressVatNumber()
        ;

        Assert::same($actualVatNumber, $vatNumber);
    }

    #[Then('/^I should be notified that the VAT number in (shipping|billing) is required$/')]
    public function iShouldBeNotifiedThatTheVatNumberIsRequired(string $type): void
    {
        $this->assertValidationMessage($type, 'Please enter a VAT number.');
    }

    #[Then('/^I should be notified that the company VAT number in (shipping|billing) is required$/')]
    public function iShouldBeNotifiedThatTheCompanyVatNumberIsRequired(string $type): void
    {
        $this->assertValidationMessage($type, 'Please enter the company VAT number.');
    }

    #[Then('/^I should be notified that the VAT number in (shipping|billing) is not valid$/')]
    public function iShouldBeNotifiedThatTheVatNumberIsNotValid(string $type): void
    {
        $this->assertValidationMessage($type, 'Please enter a valid VAT number.');
    }

    private function assertValidationMessage(string $type, string $expectedMessage): void
    {
        Assert::true(
            $this->addressPage->checkValidationMessageFor(sprintf('%s_vat_number', $type), $expectedMessage),
        );
    }
}
