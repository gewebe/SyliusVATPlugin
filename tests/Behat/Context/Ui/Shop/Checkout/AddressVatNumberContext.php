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
     * @When /^I specify the shipping vat number as "([^"]+)"$/
     */
    public function iSpecifyTheVatNumberForShippingAddress($vatNumber)
    {
        $this->addressPage->specifyShippingAddressVatNumber($vatNumber);
    }

    /**
     * @When /^I specify the billing vat number as "([^"]+)"$/
     */
    public function iSpecifyTheVatNumberForBillingAddress($vatNumber)
    {
        $this->addressPage->specifyBillingAddressVatNumber($vatNumber);
    }

    /**
     * @Then /^I should be notified that the vat number in (shipping|billing) is not valid$/
     */
    public function iShouldBeNotifiedThatTheVatNumberInShippingDetailsIsNotValid($type)
    {
        $expectedMessage = 'Please enter a valid VAT number.';
        $element = sprintf('%s_vat_number', $type);
        Assert::true($this->addressPage->checkValidationMessageFor($element, $expectedMessage));
    }
}
