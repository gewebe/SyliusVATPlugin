<?php

declare(strict_types=1);

namespace Gewebe\SyliusVATPlugin\Vat\Number\Validator;

use Gewebe\SyliusVATPlugin\Vat\Number\Hmrc\HmrcClientInterface;
use Gewebe\SyliusVATPlugin\Vat\Number\VatNumberValidatorInterface;

/**
 * United Kingdom VAT number validator, verified online through the HMRC API
 */
final class UkVatNumberValidator implements VatNumberValidatorInterface
{
    /**
     * The HMRC API only covers the United Kingdom, the country code is also the VAT number prefix
     */
    public const COUNTRY_CODE = 'GB';

    /**
     * Standard (9 digits), branch traders (12 digits),
     * government departments (GD000-GD499) and health authorities (HA500-HA999)
     */
    private const VAT_NUMBER_PATTERN = '/^(\d{9}|\d{12}|GD[0-4]\d{2}|HA[5-9]\d{2})$/';

    public function __construct(private readonly HmrcClientInterface $client)
    {
    }

    public function getCountries(): array
    {
        return [self::COUNTRY_CODE];
    }

    public function validateCountry(string $vatNumber, string $countryCode): bool
    {
        $vatNumber = $this->normalize($vatNumber);

        if (str_starts_with($vatNumber, self::COUNTRY_CODE)) {
            return strtoupper($countryCode) === self::COUNTRY_CODE;
        }

        // The country prefix is optional for UK VAT numbers
        return $this->matchesPattern($vatNumber);
    }

    public function validateFormat(string $vatNumber): bool
    {
        return $this->matchesPattern($this->stripCountryPrefix($this->normalize($vatNumber)));
    }

    public function validate(string $vatNumber): bool
    {
        $vatNumber = $this->stripCountryPrefix($this->normalize($vatNumber));

        if (false === $this->matchesPattern($vatNumber)) {
            return false;
        }

        $vatRegistrationNumber = $this->getVatRegistrationNumber($vatNumber);
        if (null === $vatRegistrationNumber) {
            // Government departments and health authorities cannot be looked up online
            return true;
        }

        return $this->client->checkVatNumber($vatRegistrationNumber);
    }

    private function normalize(string $vatNumber): string
    {
        return strtoupper((string) preg_replace('/[\s.\-]/', '', $vatNumber));
    }

    private function stripCountryPrefix(string $vatNumber): string
    {
        if (str_starts_with($vatNumber, self::COUNTRY_CODE)) {
            return substr($vatNumber, strlen(self::COUNTRY_CODE));
        }

        return $vatNumber;
    }

    private function matchesPattern(string $vatNumber): bool
    {
        return 1 === preg_match(self::VAT_NUMBER_PATTERN, $vatNumber);
    }

    /**
     * The HMRC lookup expects the 9 digit VAT registration number,
     * the last 3 digits of a branch trader number identify the branch
     */
    private function getVatRegistrationNumber(string $vatNumber): ?string
    {
        if (1 !== preg_match('/^(\d{9})(\d{3})?$/', $vatNumber, $matches)) {
            return null;
        }

        return $matches[1];
    }
}
