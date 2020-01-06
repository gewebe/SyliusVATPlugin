<?php

declare(strict_types=1);

namespace Tests\Gweb\SyliusVATPlugin\Behat\Page\Shop\Account\AddressBook;

interface CreatePageInterface extends \Sylius\Behat\Page\Shop\Account\AddressBook\CreatePageInterface
{
    /**
     * @param string $vatNumber
     */
    public function specifyVatNumber(string $vatNumber): void;

    /**
     * @return bool
     */
    public function hasVatNumberValidationMessage();
}
