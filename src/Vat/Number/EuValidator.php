<?php

declare(strict_types=1);

namespace Gewebe\SyliusVATPlugin\Vat\Number;

use Ibericode\Vat\Validator;
use Ibericode\Vat\Vies\ViesException;

/**
 * European VAT number validator
 */
final class EuValidator implements ValidatorInterface
{
    /** @var Validator */
    private $validator;

    public function __construct(Validator $validator)
    {
        $this->validator = $validator;
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
