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
    /** @var VatNumberValidatorProviderInterface */
    private $validatorProvider;

    /** @var VatNumberValidatorInterface|null */
    private $validator;

    /** @var bool */
    private $isActive = true;

    /** @var bool */
    private $validateCountry = true;

    /** @var bool */
    private $validateExistence = true;

    public function __construct(
        VatNumberValidatorProviderInterface $validatorProvider,
        bool $isActive = true,
        bool $validateCountry = true,
        bool $validateExistence = true
    ) {
        $this->validatorProvider = $validatorProvider;
        $this->isActive = $isActive;
        $this->validateCountry = $validateCountry;
        $this->validateExistence = $validateExistence;
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$this->isActive) {
            return;
        }

        if (!$constraint instanceof VatNumber) {
            throw new UnexpectedTypeException($constraint, VatNumber::class);
        }

        if (!$value instanceof VatNumberAddressInterface) {
            throw new UnexpectedValueException($value, VatNumberAddressInterface::class);
        }

        if (null === $value->getVatNumber() || '' === $value->getVatNumber()) {
            return;
        }

        if (!$this->setValidator($value)) {
            return;
        }

        $this->validateVatNumberAddress($value, $constraint);
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
        if ($this->validator === null) {
            return false;
        }

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
        if ($this->validator === null) {
            return false;
        }

        try {
            $vatNumber = $address->getVatNumber();

            $valid = !is_null($vatNumber) && $this->validator->validate($vatNumber);

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
