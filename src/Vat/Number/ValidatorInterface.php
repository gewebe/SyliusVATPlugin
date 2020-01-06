<?php

declare(strict_types=1);

namespace Gweb\SyliusVATPlugin\Vat\Number;

/**
 * VAT number validator
 * @package Gweb\SyliusVATPlugin\Validator
 */
interface ValidatorInterface
{
    /**
     * Validate VAT number country
     * @param string $vatNumber
     * @param string $countryCode ISO-3166-1-alpha2 country code
     * @return bool
     */
    public function validateCountry(string $vatNumber, string $countryCode): bool;

    /**
     * Validate VAT number format
     * @param string $vatNumber
     * @return bool
     */
    public function validateFormat(string $vatNumber): bool;

    /**
     * Validate VAT number format and existence
     * @param string $vatNumber
     * @return bool
     *
     * @throws ClientException
     */
    public function validate(string $vatNumber): bool;
}
