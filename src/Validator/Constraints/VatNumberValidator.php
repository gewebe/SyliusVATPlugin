<?php

declare(strict_types=1);

namespace Gewebe\SyliusVATPlugin\Validator\Constraints;

use Gewebe\SyliusVATPlugin\Entity\VatNumberAddressInterface;
use Gewebe\SyliusVATPlugin\Vat\Number\ClientException;
use Gewebe\SyliusVATPlugin\Vat\Number\ValidatorInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class VatNumberValidator extends ConstraintValidator
{
    /** @var ValidatorInterface */
    private $validator;

    /** @var bool */
    private $isActive = true;

    /** @var bool */
    private $validateCountry = true;

    /** @var bool */
    private $validateExistence = true;

    public function __construct(
        ValidatorInterface $validator,
        bool $isActive = true,
        bool $validateCountry = true,
        bool $validateExistence = true
    ) {
        $this->validator = $validator;
        $this->isActive = $isActive;
        $this->validateCountry = $validateCountry;
        $this->validateExistence = $validateExistence;
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$this->isActive) {
            return;
        }

        if (!$value instanceof VatNumberAddressInterface) {
            throw new UnexpectedValueException($value, VatNumberAddressInterface::class);
        }

        $address = $value;

        if (!$constraint instanceof VatNumber) {
            throw new UnexpectedTypeException($constraint, VatNumber::class);
        }

        if (null === $address->getVatNumber() || '' === $address->getVatNumber()) {
            return;
        }

        if (!$this->validateFormat($address, $constraint)) {
            return;
        }

        if ($this->validateCountry && !$this->validateCountry($address, $constraint)) {
            return;
        }

        if ($this->validateExistence) {
            $this->validateExistence($address, $constraint);
        }
    }

    /**
     * check vat number format
     */
    private function validateFormat(VatNumberAddressInterface $address, VatNumber $constraint): bool
    {
        $vatNumber = $address->getVatNumber();

        if (is_null($vatNumber) || !$this->validator->validateFormat($vatNumber)) {
            $this->context->buildViolation($constraint->messageFormat)
                ->atPath($constraint->vatNumberPath)
                ->addViolation();
            return false;
        }
        return true;
    }

    /**
     * check vat number country is same as address country
     */
    private function validateCountry(VatNumberAddressInterface $address, VatNumber $constraint): bool
    {
        $vatNumber = $address->getVatNumber();
        $countryCode = $address->getCountryCode();

        if (is_null($vatNumber)
            || is_null($countryCode)
            || !$this->validator->validateCountry($vatNumber, $countryCode)) {
            $this->context->buildViolation($constraint->messageCountry)
                ->atPath($constraint->vatNumberPath)
                ->addViolation();
            return false;
        }
        return true;
    }

    /**
     * check vat number existence
     */
    private function validateExistence(VatNumberAddressInterface $address, VatNumber $constraint): bool
    {
        try {
            $vatNumber = $address->getVatNumber();

            $valid = is_null($vatNumber) ? false : $this->validator->validate($vatNumber);

            $address->setVatValid($valid);
        } catch (ClientException $e) {
            // ignore VAT client exceptions (when the service is down)
            // this could mean that an unexisting VAT number passes validation,
            // but it's (probably) better than a hard-error
            return true;
        }

        if (false === $valid) {
            $this->context->buildViolation($constraint->messageVerified)
                ->atPath($constraint->vatNumberPath)
                ->addViolation();
            return false;
        }
        return true;
    }
}
