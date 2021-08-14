<?php

declare(strict_types=1);

namespace Gewebe\SyliusVATPlugin\Vat\Number\Validator;

use Gewebe\SyliusVATPlugin\Vat\Number\ClientException;
use Gewebe\SyliusVATPlugin\Vat\Number\VatNumberValidatorInterface;
use Ibericode\Vat\Countries;
use Ibericode\Vat\Validator;
use Ibericode\Vat\Vies\ViesException;

/**
 * European VAT number validator
 */
final class EuVatNumberValidator implements VatNumberValidatorInterface
{
    /** @var Validator */
    private $validator;

    public function __construct(Validator $validator)
    {
        $this->validator = $validator;
    }

    public function getCountries(): array
    {
        $countries = new Countries();
        $euCountries = [];

        /** @var string $countryCode */
        foreach (array_keys(iterator_to_array($countries)) as $countryCode) {
            if ($countries->isCountryCodeInEU($countryCode)) {
                $euCountries[] = $countryCode;
            }
        }

        return $euCountries;
    }

    public function validateCountry(string $vatNumber, string $countryCode): bool
    {
        $country = substr($vatNumber, 0, 2);

        if (strtolower($country) === strtolower($countryCode)) {
            return true;
        }
        return false;
    }

    public function validateFormat(string $vatNumber): bool
    {
        return $this->validator->validateVatNumberFormat($vatNumber);
    }

    public function validate(string $vatNumber): bool
    {
        try {
            return $this->validator->validateVatNumber($vatNumber);
        } catch (ViesException $e) {
            throw new ClientException($e->getMessage(), (int) $e->getCode());
        }
    }
}
