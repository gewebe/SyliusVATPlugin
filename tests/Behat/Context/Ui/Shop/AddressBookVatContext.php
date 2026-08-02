<?php

declare(strict_types=1);

namespace Tests\Gewebe\SyliusVATPlugin\Behat\Context\Ui\Shop;

use Behat\Behat\Context\Context;
use Behat\Step\Then;
use Behat\Step\When;
use Tests\Gewebe\SyliusVATPlugin\Behat\Page\Shop\Account\AddressBook\CreatePageInterface;
use Webmozart\Assert\Assert;

final class AddressBookVatContext implements Context
{
    public function __construct(private readonly CreatePageInterface $createPage)
    {
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
}
