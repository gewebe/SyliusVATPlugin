<?php

declare(strict_types=1);

namespace Gewebe\SyliusVATPlugin\Validator\Constraints;

use Gewebe\SyliusVATPlugin\Entity\VatNumberAddressInterface;
use Gewebe\SyliusVATPlugin\Vat\Number\ClientException;
use Gewebe\SyliusVATPlugin\Vat\Number\VatNumberValidatorInterface;
use Gewebe\SyliusVATPlugin\Vat\Number\VatNumberValidatorProviderInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class VatNumberValidator extends ConstraintValidator
{
    private ?VatNumberValidatorInterface $validator = null;

    public function __construct(
        private VatNumberValidatorProviderInterface $validatorProvider,
        private bool $isActive = true,
        private bool $validateCountry = true,
        private bool $validateExistence = true,
        private bool $isCompanyVatRequired = true,
        private array $requiredCountries = [],
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

        if (!$this->hasVatNumberForCompany($value, $constraint)) {
            return;
        }

        if (!$this->hasVatNumberForCountry($value, $constraint)) {
            return;
        }

        if (!$value->hasVatNumber()) {
            return;
        }

        $this->validateVatNumberAddress($value, $constraint);
    }

    private function hasVatNumberForCompany(VatNumberAddressInterface $address, VatNumber $constraint): bool
    {
        if ($this->isCompanyVatRequired &&
            null !== $address->getCompany() &&
            '' !== $address->getCompany() &&
            !$address->hasVatNumber()) {
            $this->context->buildViolation($constraint->messageRequiredForCompany)
                ->atPath($constraint->vatNumberPath)
                ->addViolation()
            ;

            return false;
        }

        return true;
    }

    private function hasVatNumberForCountry(VatNumberAddressInterface $address, VatNumber $constraint): bool
    {
        if (count($this->requiredCountries) > 0 &&
            in_array($address->getCountryCode(), $this->requiredCountries, true) &&
            !$address->hasVatNumber()) {
            $this->context->buildViolation($constraint->messageRequired)
                ->atPath($constraint->vatNumberPath)
                ->addViolation()
            ;

            return false;
        }

        return true;
    }

    private function setValidator(VatNumberAddressInterface $address): bool
    {
        $countryCode = $address->getCountryCode();
        if (null === $countryCode || '' === $countryCode) {
            return false;
        }

        $this->validator = $this->validatorProvider->getValidator($countryCode);
        if ($this->validator instanceof VatNumberValidatorInterface) {
            return true;
        }

        return false;
    }

    private function validateVatNumberAddress(VatNumberAddressInterface $address, VatNumber $constraint): bool
    {
        if (!$this->isActive) {
            return false;
        }

        if (!$this->setValidator($address)) {
            return false;
        }

        if (!$this->validateFormat($address, $constraint)) {
            return false;
        }

        if ($this->validateCountry && !$this->validateCountry($address, $constraint)) {
            return false;
        }

        if ($this->validateExistence) {
            return $this->validateExistence($address, $constraint);
        }

        return false;
    }

    /**
     * check vat number format
     */
    private function validateFormat(VatNumberAddressInterface $address, VatNumber $constraint): bool
    {
        if ($this->validator === null) {
            return false;
        }

        $vatNumber = $address->getVatNumber();

        if (null === $vatNumber || !$this->validator->validateFormat($vatNumber)) {
            $this->context->buildViolation($constraint->messageFormat)
                ->atPath($constraint->vatNumberPath)
                ->addViolation()
            ;

            return false;
        }

        return true;
    }

    /**
     * check vat number country is same as address country
     */
    private function validateCountry(VatNumberAddressInterface $address, VatNumber $constraint): bool
    {
        if ($this->validator === null) {
            return false;
        }

        $vatNumber = $address->getVatNumber();
        $countryCode = $address->getCountryCode();

        if (null === $vatNumber ||
            null === $countryCode ||
            !$this->validator->validateCountry($vatNumber, $countryCode)) {
            $this->context->buildViolation($constraint->messageCountry)
                ->atPath($constraint->vatNumberPath)
                ->addViolation()
            ;

            return false;
        }

        return true;
    }

    /**
     * check vat number existence
     */
    private function validateExistence(VatNumberAddressInterface $address, VatNumber $constraint): bool
    {
        if ($this->validator === null) {
            return false;
        }

        try {
            $vatNumber = $address->getVatNumber();

            $valid = null !== $vatNumber && $this->validator->validate($vatNumber);

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
                ->addViolation()
            ;

            return false;
        }

        return true;
    }
}
