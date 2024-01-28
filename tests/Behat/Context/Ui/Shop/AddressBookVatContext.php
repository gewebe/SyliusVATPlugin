<?php

declare(strict_types=1);

namespace Tests\Gewebe\SyliusVATPlugin\Behat\Context\Ui\Shop;

use Behat\Behat\Context\Context;
use Tests\Gewebe\SyliusVATPlugin\Behat\Page\Shop\Account\AddressBook\CreatePageInterface;
use Webmozart\Assert\Assert;

final class AddressBookVatContext implements Context
{
    public function __construct(private CreatePageInterface $createPage)
    {
    }

    /**
     * @When /^I specify my company as "([^"]+)"$/
     */
    public function iSpecifyMyVCompany($company)
    {
        $this->createPage->specifyCompany($company);
    }

    /**
     * @When /^I specify my vat number as "([^"]+)"$/
     */
    public function iSpecifyMyVatNumber($vatNumber)
    {
        $this->createPage->specifyVatNumber($vatNumber);
    }

    /**
     * @Then I should be notified that the vat number is not valid
     */
    public function iShouldBeNotifiedThatTheVatNumberIsNotValid()
    {
        Assert::true($this->createPage->hasVatNumberValidationMessage());
    }
}
