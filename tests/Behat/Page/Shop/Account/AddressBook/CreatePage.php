<?php

declare(strict_types=1);

namespace Tests\Gewebe\SyliusVATPlugin\Behat\Page\Shop\Account\AddressBook;

use Sylius\Behat\Page\Shop\Account\AddressBook\CreatePage as BaseCreatePage;

class CreatePage extends BaseCreatePage implements CreatePageInterface
{
    public function specifyCompany(string $company): void
    {
        $this->getElement('company')->setValue($company);
    }

    public function specifyVatNumber(string $vatNumber): void
    {
        $this->getElement('vat_number')->setValue($vatNumber);
    }

    public function hasVatNumberValidationMessage(): bool
    {
        return null !== $this->getDocument()->find('css', '.sylius-validation-error:contains("vatNumber")');
    }

    protected function getDefinedElements(): array
    {
        return array_merge(parent::getDefinedElements(), [
            'company' => '[data-test-company]',
            'vat_number' => '[data-test-vatNumber]',
        ]);
    }
}
