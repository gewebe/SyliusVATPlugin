<?php

declare(strict_types=1);

namespace Tests\Gewebe\SyliusVATPlugin\Behat\Page\Shop\Account\AddressBook;

class CreatePage extends \Sylius\Behat\Page\Shop\Account\AddressBook\CreatePage implements CreatePageInterface
{
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
     * {@inheritdoc}
     */
    protected function getDefinedElements(): array
    {
        return array_merge(parent::getDefinedElements(), [
            'vat_number' => '#sylius_address_vatNumber',
        ]);
    }
}
