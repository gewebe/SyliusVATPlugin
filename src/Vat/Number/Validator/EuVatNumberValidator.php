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
    public function __construct(
        private readonly Validator $validator,
        private readonly Countries $countries = new Countries(),
    ) {
    }

    /**
     * @return list<string>
     */
    public function getCountries(): array
    {
        $euCountries = [];

        /** @var string $countryCode */
        foreach (array_keys(iterator_to_array($this->countries)) as $countryCode) {
            if ($this->countries->isCountryCodeInEU($countryCode)) {
                $euCountries[] = $countryCode;
            }
        }

        return $euCountries;
    }

    public function validateCountry(string $vatNumber, string $countryCode): bool
    {
        return 0 === strcasecmp(substr($vatNumber, 0, 2), $countryCode);
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
            throw new ClientException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
