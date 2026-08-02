<?php

declare(strict_types=1);

namespace Tests\Gewebe\SyliusVATPlugin\Behat\Context\Ui\Admin;

use Behat\Behat\Context\Context;
use Behat\Step\Then;
use Behat\Step\When;
use InvalidArgumentException;
use Tests\Gewebe\SyliusVATPlugin\Behat\Page\Admin\Order\ShowPageInterface;
use Tests\Gewebe\SyliusVATPlugin\Behat\Page\Admin\Order\UpdatePageInterface;
use Webmozart\Assert\Assert;

class ManagingOrdersVatContext implements Context
{
    public function __construct(
        private readonly ShowPageInterface $showPage,
        private readonly UpdatePageInterface $updatePage,
    ) {
    }

    #[Then('/^I should see (valid|invalid|unverified) VAT number "([^"]+)" in the billing address$/')]
    public function iShouldSeeVatNumberInTheBillingAddress(string $vatValidation, string $vatNumber): void
    {
        Assert::true($this->showPage->hasBillingVatNumber($vatNumber));

        Assert::eq($this->showPage->getBillingVatValidation(), $this->getExpectedValidationMessage($vatValidation));
    }

    #[Then('/^I should see (valid|invalid|unverified) VAT number "([^"]+)" in the shipping address$/')]
    public function iShouldSeeVatNumberInTheShippingAddress(string $vatValidation, string $vatNumber): void
    {
        Assert::true($this->showPage->hasShippingVatNumber($vatNumber));

        Assert::eq($this->showPage->getShippingVatValidation(), $this->getExpectedValidationMessage($vatValidation));
    }

    #[When('/^I do specify billing address VAT number to "([^"]+)"$/')]
    public function iSpecifyBillingAddressVatNumber(string $vatNumber): void
    {
        $this->updatePage->specifyBillingAddressVatNumber($vatNumber);
    }

    #[When('/^I do specify shipping address VAT number to "([^"]+)"$/')]
    public function iSpecifyShippingAddressVatNumber(string $vatNumber): void
    {
        $this->updatePage->specifyShippingAddressVatNumber($vatNumber);
    }

    private function getExpectedValidationMessage(string $vatValidation): string
    {
        return match ($vatValidation) {
            'valid' => 'VAT number is valid',
            'invalid' => 'VAT number is invalid',
            'unverified' => 'VAT number not validated yet',
            default => throw new InvalidArgumentException(
                sprintf('Unknown VAT validation state "%s".', $vatValidation),
            ),
        };
    }
}
