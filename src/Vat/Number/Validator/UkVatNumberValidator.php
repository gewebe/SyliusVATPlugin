<?php

declare(strict_types=1);

namespace Gewebe\SyliusVATPlugin\Vat\Number\Validator;

use Gewebe\SyliusVATPlugin\Vat\Number\ClientException;
use Gewebe\SyliusVATPlugin\Vat\Number\Hmrc\HmrcClientInterface;
use Gewebe\SyliusVATPlugin\Vat\Number\VatNumberValidatorInterface;
use Ibericode\Vat\Validator;
use Ibericode\Vat\Vies\ViesException;

/**
 * United Kingdom VAT number validator: GB numbers use HMRC, Northern Ireland XI numbers use VIES
 */
final class UkVatNumberValidator implements VatNumberValidatorInterface
{
    /**
     * ISO country code used by addresses in Great Britain and Northern Ireland
     */
    public const COUNTRY_CODE = 'GB';

    public const NORTHERN_IRELAND_PREFIX = 'XI';

    /**
     * Standard (9 digits), branch traders (12 digits),
     * government departments (GD000-GD499) and health authorities (HA500-HA999)
     */
    private const VAT_NUMBER_PATTERN = '/^(\d{9}|\d{12}|GD[0-4]\d{2}|HA[5-9]\d{2})$/';

    public function __construct(
        private readonly HmrcClientInterface $client,
        private readonly Validator $viesValidator = new Validator(),
    ) {
    }

    public function getCountries(): array
    {
        return [self::COUNTRY_CODE];
    }

    public function validateCountry(string $vatNumber, string $countryCode): bool
    {
        $vatNumber = $this->normalize($vatNumber);

        if (str_starts_with($vatNumber, self::NORTHERN_IRELAND_PREFIX)) {
            return strtoupper($countryCode) === self::COUNTRY_CODE;
        }

        if (str_starts_with($vatNumber, self::COUNTRY_CODE)) {
            return strtoupper($countryCode) === self::COUNTRY_CODE;
        }

        // The country prefix is optional for UK VAT numbers
        return $this->matchesPattern($vatNumber);
    }

    public function validateFormat(string $vatNumber): bool
    {
        $vatNumber = $this->normalize($vatNumber);

        if (str_starts_with($vatNumber, self::NORTHERN_IRELAND_PREFIX)) {
            return $this->viesValidator->validateVatNumberFormat($vatNumber);
        }

        return $this->matchesPattern($this->stripCountryPrefix($vatNumber));
    }

    public function validate(string $vatNumber): bool
    {
        $vatNumber = $this->normalize($vatNumber);

        if (str_starts_with($vatNumber, self::NORTHERN_IRELAND_PREFIX)) {
            try {
                return $this->viesValidator->validateVatNumber($vatNumber);
            } catch (ViesException $exception) {
                throw new ClientException($exception->getMessage(), $exception->getCode(), $exception);
            }
        }

        $vatNumber = $this->stripCountryPrefix($vatNumber);

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
