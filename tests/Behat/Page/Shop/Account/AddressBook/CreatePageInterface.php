<?php

declare(strict_types=1);

namespace Tests\Gewebe\SyliusVATPlugin\Behat\Page\Shop\Account\AddressBook;

interface CreatePageInterface extends \Sylius\Behat\Page\Shop\Account\AddressBook\CreatePageInterface
{
    public function specifyVatNumber(string $vatNumber): void;

    /**
     * @return bool
     */
    public function hasVatNumberValidationMessage();
}
