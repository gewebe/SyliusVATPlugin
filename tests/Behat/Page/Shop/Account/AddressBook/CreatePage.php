<?php

declare(strict_types=1);

namespace Tests\Gewebe\SyliusVATPlugin\Behat\Page\Shop\Account\AddressBook;

class CreatePage extends \Sylius\Behat\Page\Shop\Account\AddressBook\CreatePage implements CreatePageInterface
{
    /**
     * @inheritdoc
     */
    public function specifyCompany(string $company): void
    {
        $this->getElement('company')->setValue($company);
    }

    /**
     * @inheritDoc
     */
    public function specifyVatNumber(string $vatNumber): void
    {
        $this->getElement('vat_number')->setValue($vatNumber);
    }

    /**
     * @inheritDoc
     */
    public function hasVatNumberValidationMessage()
    {
        return null !== $this->getDocument()->find('css', '.sylius-validation-error:contains("vatNumber")');
    }

    /**
     * @inheritdoc
     */
    protected function getDefinedElements(): array
    {
        return array_merge(parent::getDefinedElements(), [
            'company' => '#sylius_address_company',
            'vat_number' => '#sylius_address_vatNumber',
        ]);
    }
}
