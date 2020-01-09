<?php

declare(strict_types=1);

namespace Tests\Gweb\SyliusVATPlugin\Behat\Context\Ui\Admin;

use Behat\Behat\Context\Context;
use Tests\Gweb\SyliusVATPlugin\Behat\Page\Admin\Order\ShowPageInterface;
use Tests\Gweb\SyliusVATPlugin\Behat\Page\Admin\Order\UpdatePageInterface;
use Webmozart\Assert\Assert;

class ManagingOrdersVatContext implements Context
{
    /** @var ShowPageInterface */
    private $showPage;

    /** @var UpdatePageInterface */
    private $updatePage;

    public function __construct(
        ShowPageInterface $showPage,
        UpdatePageInterface $updatePage
    ) {
        $this->showPage = $showPage;
        $this->updatePage = $updatePage;
    }

    /**
     * @Then /^I should see (valid|invalid|unverified) VAT number "([^"]+)" in the billing address$/
     */
    public function iShouldSeeVatNumberInTheBillingAddress($vatValidation, $vatNumber)
    {
        Assert::true($this->showPage->hasBillingVatNumber($vatNumber));

        if ($vatValidation == 'valid') {
            Assert::eq($this->showPage->getBillingVatValidation(), 'VAT number is valid');
        }
        elseif ($vatValidation == 'invalid') {
            Assert::eq($this->showPage->getBillingVatValidation(), 'VAT number is invalid');
        }
        elseif ($vatValidation == 'unverified') {
            Assert::eq($this->showPage->getBillingVatValidation(), 'VAT number not validated yet');
        }
    }

    /**
     * @Then /^I should see (valid|invalid|unverified) VAT number "([^"]+)" in the shipping address$/
     */
    public function iShouldSeeVatNumberInTheShippingAddress($vatValidation, $vatNumber)
    {
        Assert::true($this->showPage->hasShippingVatNumber($vatNumber));

        if ($vatValidation == 'valid') {
            Assert::eq($this->showPage->getShippingVatValidation(), 'VAT number is valid');
        }
        elseif ($vatValidation == 'invalid') {
            Assert::eq($this->showPage->getShippingVatValidation(), 'VAT number is invalid');
        }
        elseif ($vatValidation == 'unverified') {
            Assert::eq($this->showPage->getShippingVatValidation(), 'VAT number not validated yet');
        }
    }

    /**
     * @When /^I do specify billing address VAT number to "([^"]+)"$/
     */
    public function iSpecifyBillingAddressVatNumber($vatNumber)
    {
        $this->updatePage->specifyBillingAddressVatNumber($vatNumber);
    }

    /**
     * @When /^I do specify shipping address VAT number to "([^"]+)"$/
     */
    public function iSpecifyShippingAddressVatNumber($vatNumber)
    {
        $this->updatePage->specifyShippingAddressVatNumber($vatNumber);
    }
}
