<?php

declare(strict_types=1);

namespace Gewebe\SyliusVATPlugin\Validator\Constraints;

use Gewebe\SyliusVATPlugin\Config\VatNumberValidatorConfig;
use Gewebe\SyliusVATPlugin\Entity\VatNumberAddressInterface;
use Gewebe\SyliusVATPlugin\Vat\Number\ClientException;
use Gewebe\SyliusVATPlugin\Vat\Number\VatNumberValidatorInterface;
use Gewebe\SyliusVATPlugin\Vat\Number\VatNumberValidatorProviderInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class VatNumberValidator extends ConstraintValidator
{
    public function __construct(
        private readonly VatNumberValidatorProviderInterface $validatorProvider,
        private readonly VatNumberValidatorConfig $validatorConfig,
    ) {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof VatNumber) {
            throw new UnexpectedTypeException($constraint, VatNumber::class);
        }

        if (!$value instanceof VatNumberAddressInterface) {
            throw new UnexpectedValueException($value, VatNumberAddressInterface::class);
        }

        if ($this->hasVatNumberForCompany($value, $constraint) === false ||
            $this->hasVatNumberForCountry($value, $constraint) === false ||
            $this->validatorConfig->validateFormat === false ||
            $value->getVatNumber() === null ||
            $value->getVatNumber() === '' ||
            $value->getCountryCode() === null
        ) {
            return;
        }

        $vatNumberValidator = $this->validatorProvider->getValidator($value->getCountryCode());
        if (null === $vatNumberValidator) {
            return;
        }

        if ($vatNumberValidator->validateFormat($value->getVatNumber()) === false) {
            $this->addViolation($constraint->messageInvalidFormat, $constraint->vatNumberPath);

            return;
        }

        if ($this->validatorConfig->validateCountry &&
            $vatNumberValidator->validateCountry($value->getVatNumber(), $value->getCountryCode()) === false
        ) {
            $this->addViolation($constraint->messageInvalidCountry, $constraint->vatNumberPath);

            return;
        }

        if ($this->validatorConfig->validateRegistration) {
            $this->validateRegistration($value, $vatNumberValidator, $constraint);
        }
    }

    private function addViolation(string $message, string $path): void
    {
        $this->context->buildViolation($message)->atPath($path)->addViolation();
    }

    private function hasVatNumberForCompany(VatNumberAddressInterface $address, VatNumber $constraint): bool
    {
        if ($this->validatorConfig->isRequiredForCompany &&
            $address->getCompany() !== null &&
            $address->getCompany() !== '' &&
            $address->hasVatNumber() === false
        ) {
            $this->addViolation($constraint->messageRequiredForCompany, $constraint->vatNumberPath);

            return false;
        }

        return true;
    }

    private function hasVatNumberForCountry(VatNumberAddressInterface $address, VatNumber $constraint): bool
    {
        if (count($this->validatorConfig->requiredForCountries) > 0 &&
            in_array($address->getCountryCode(), $this->validatorConfig->requiredForCountries, true) &&
            $address->hasVatNumber() === false
        ) {
            $this->addViolation($constraint->messageRequired, $constraint->vatNumberPath);

            return false;
        }

        return true;
    }

    private function validateRegistration(
        VatNumberAddressInterface $address,
        VatNumberValidatorInterface $validator,
        VatNumber $constraint,
    ): void {
        try {
            $validVatNumber = $validator->validate($address->getVatNumber() ?? '');
        } catch (ClientException) {
            if ($this->validatorConfig->validateOnServiceUnavailable === true) {
                $this->addViolation($constraint->messageServiceUnavailable, $constraint->vatNumberPath);
            }

            return;
        }

        $address->setVatValid($validVatNumber);

        if (false === $validVatNumber) {
            $this->addViolation($constraint->messageInvalidRegistration, $constraint->vatNumberPath);
        }
    }
}
