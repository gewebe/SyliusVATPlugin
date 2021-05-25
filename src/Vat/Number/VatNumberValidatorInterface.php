<?php

declare(strict_types=1);

namespace Gewebe\SyliusVATPlugin\Vat\Number;

/**
 * VAT number validator
 * @package Gewebe\SyliusVATPlugin\Validator
 */
interface VatNumberValidatorInterface
{
    /**
     * ISO-3166-1-alpha2 country codes for validation
     * @return array
     */
    public function getCountries(): array;

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
