<?php

declare(strict_types=1);

namespace Tests\Gewebe\SyliusVATPlugin\Behat\Context\Ui\Shop\Checkout;

use Behat\Behat\Context\Context;
use Tests\Gewebe\SyliusVATPlugin\Behat\Page\Shop\Checkout\AddressPage;
use Webmozart\Assert\Assert;

final class AddressVatNumberContext implements Context
{
    public function __construct(private AddressPage $addressPage)
    {
    }

    /**
     * @When /^I specify the billing company as "([^"]+)"$/
     */
    public function iSpecifyTheCompanyForBillingAddress($company)
    {
        $this->addressPage->specifyBillingAddressCompany($company);
    }

    /**
     * @When /^I specify the billing vat number as "([^"]+)"$/
     */
    public function iSpecifyTheVatNumberForBillingAddress($vatNumber)
    {
        $this->addressPage->specifyBillingAddressVatNumber($vatNumber);
    }

    /**
     * @When /^I specify the shipping company as "([^"]+)"$/
     */
    public function iSpecifyTheCompanyForShippingAddress($company)
    {
        $this->addressPage->specifyShippingAddressCompany($company);
    }

    /**
     * @When /^I specify the shipping vat number as "([^"]+)"$/
     */
    public function iSpecifyTheVatNumberForShippingAddress($vatNumber)
    {
        $this->addressPage->specifyShippingAddressVatNumber($vatNumber);
    }

    /**
     * @Then /^I should be notified that the vat number in (shipping|billing) is required$/
     */
    public function iShouldBeNotifiedThatTheVatNumberIsRequired($type)
    {
        $expectedMessage = 'Please enter a VAT number.';
        $element = sprintf('%s_vat_number', $type);
        Assert::true($this->addressPage->checkValidationMessageFor($element, $expectedMessage));
    }

    /**
     * @Then /^I should be notified that the company vat number in (shipping|billing) is required$/
     */
    public function iShouldBeNotifiedThatTheCompanyVatNumberIsRequired($type)
    {
        $expectedMessage = 'Please enter the company VAT number.';
        $element = sprintf('%s_vat_number', $type);
        Assert::true($this->addressPage->checkValidationMessageFor($element, $expectedMessage));
    }

    /**
     * @Then /^I should be notified that the vat number in (shipping|billing) is not valid$/
     */
    public function iShouldBeNotifiedThatTheVatNumberIsNotValid($type)
    {
        $expectedMessage = 'Please enter a valid VAT number.';
        $element = sprintf('%s_vat_number', $type);
        Assert::true($this->addressPage->checkValidationMessageFor($element, $expectedMessage));
    }
}
