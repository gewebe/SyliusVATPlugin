<?php

declare(strict_types=1);

namespace Gewebe\SyliusVATPlugin\Vat\Number;

/**
 * VAT number validator
 */
interface VatNumberValidatorInterface
{
    /**
     * ISO-3166-1-alpha2 country codes for validation
     */
    public function getCountries(): array;

    /**
     * Validate VAT number country
     *
     * @param string $countryCode ISO-3166-1-alpha2 country code
     */
    public function validateCountry(string $vatNumber, string $countryCode): bool;

    /**
     * Validate VAT number format
     */
    public function validateFormat(string $vatNumber): bool;

    /**
     * Validate VAT number format and existence
     *
     * @throws ClientException
     */
    public function validate(string $vatNumber): bool;
}
