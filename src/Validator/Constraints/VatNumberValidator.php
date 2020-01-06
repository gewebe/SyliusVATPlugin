<?php

declare(strict_types=1);

namespace Gweb\SyliusVATPlugin\Validator\Constraints;

use Gweb\SyliusVATPlugin\Entity\VatNumberAddressInterface;
use Gweb\SyliusVATPlugin\Vat\Number\ClientException;
use Gweb\SyliusVATPlugin\Vat\Number\ValidatorInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class VatNumberValidator extends ConstraintValidator
{
    /** @var ValidatorInterface */
    private $validator;

    /** @var bool */
    private $validateFormat = true;

    /** @var bool */
    private $validateCountry = true;

    /** @var bool */
    private $validateExistence = true;

    public function __construct(
        ValidatorInterface $validator,
        bool $validateFormat,
        bool $validateCountry,
        bool $validateExistence
    ) {
        $this->validator = $validator;
        $this->validateFormat = $validateFormat;
        $this->validateCountry = $validateCountry;
        $this->validateExistence = $validateExistence;
    }

    public function validate($address, Constraint $constraint)
    {
        // need value only as address entity with vat
        if (!$address instanceof VatNumberAddressInterface) {
            #throw new UnexpectedValueException($address, VatNumberAddressInterface::class);
            return;
        }

        if (!$constraint instanceof VatNumber) {
            throw new UnexpectedTypeException($constraint, VatNumber::class);
        }

        // custom constraints should ignore null and empty values to allow
        // other constraints (NotBlank, NotNull, etc.) take care of that
        if (null === $address->getVatNumber() || '' === $address->getVatNumber()) {
            return;
        }

        if (!$this->validateFormat || !$this->validateFormat($address, $constraint)) {
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
     * @param VatNumberAddressInterface $address
     * @param VatNumber $constraint
     * @return bool
     */
    private function validateFormat(VatNumberAddressInterface $address, VatNumber $constraint): bool
    {
        if (!$this->validator->validateFormat($address->getVatNumber())) {
            $this->context->buildViolation($constraint->messageFormat)
                ->atPath($constraint->vatNumberPath)
                ->addViolation();
            return false;
        }
        return true;
    }

    /**
     * check vat number country is same as address country
     * @param VatNumberAddressInterface $address
     * @param VatNumber $constraint
     * @return bool
     */
    private function validateCountry(VatNumberAddressInterface $address, VatNumber $constraint): bool
    {
        if (!$this->validator->validateCountry($address->getVatNumber(), $address->getCountryCode())) {
            $this->context->buildViolation($constraint->messageCountry)
                ->atPath($constraint->vatNumberPath)
                ->addViolation();
            return false;
        }
        return true;
    }

    /**
     * check vat number existence
     * @param VatNumberAddressInterface $address
     * @param VatNumber $constraint
     * @return bool
     */
    private function validateExistence(VatNumberAddressInterface $address, VatNumber $constraint): bool
    {
        try {
            $valid = $this->validator->validate($address->getVatNumber());

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
