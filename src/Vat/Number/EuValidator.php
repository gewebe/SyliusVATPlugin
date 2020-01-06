<?php

declare(strict_types=1);

namespace Gweb\SyliusVATPlugin\Vat\Number;

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

    /** @inheritDoc */
    public function validateCountry(string $vatNumber, string $countryCode): bool
    {
        $country = substr($vatNumber, 0, 2);

        if (strtolower($country) === strtolower($countryCode)) {
            return true;
        }
        return false;
    }

    /** @inheritDoc */
    public function validateFormat(string $vatNumber): bool
    {
        return $this->validator->validateVatNumberFormat($vatNumber);
    }

    /** @inheritDoc */
    public function validate(string $vatNumber): bool
    {
        try {
            return $this->validator->validateVatNumber($vatNumber);
        } catch (ViesException $e) {
            throw new ClientException($e->getMessage(), $e->getCode());
        }
    }
}
