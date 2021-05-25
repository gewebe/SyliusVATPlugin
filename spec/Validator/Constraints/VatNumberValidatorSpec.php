<?php

declare(strict_types=1);

namespace spec\Gewebe\SyliusVATPlugin\Validator\Constraints;

use Gewebe\SyliusVATPlugin\Entity\VatNumberAddressInterface;
use Gewebe\SyliusVATPlugin\Validator\Constraints\VatNumber;
use Gewebe\SyliusVATPlugin\Vat\Number\ClientException;
use Gewebe\SyliusVATPlugin\Vat\Number\VatNumberValidatorInterface;
use Gewebe\SyliusVATPlugin\Vat\Number\VatNumberValidatorProviderInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class VatNumberValidatorSpec extends ObjectBehavior
{
    private const VAT_VALID = 'DE118716043';
    private const VAT_VALID_FORMAT = 'DE999999999';
    private const VAT_VALID_SERVICE_UNAVAILABLE = 'DE123123123';
    private const VAT_INVALID_COUNTRY = 'ATU99999999';
    private const VAT_INVALID = 'XY123';

    private $validator;

    function let(
        VatNumberValidatorInterface $validator,
        VatNumberValidatorProviderInterface $validatorProvider
    ) {
        $validatorProvider->getValidator('DE')->willReturn($validator);
        $validatorProvider->getValidator('XY')->willReturn(null);

        $validator->validateFormat(self::VAT_INVALID)->willReturn(false);
        $validator->validateFormat(self::VAT_INVALID_COUNTRY)->willReturn(true);
        $validator->validateCountry(self::VAT_INVALID_COUNTRY, 'DE')->willReturn(false);
        $validator->validateFormat(self::VAT_VALID_FORMAT)->willReturn(true);
        $validator->validateCountry(self::VAT_VALID_FORMAT, 'DE')->willReturn(true);
        $validator->validateFormat(self::VAT_VALID_SERVICE_UNAVAILABLE)->willReturn(true);
        $validator->validateCountry(self::VAT_VALID_SERVICE_UNAVAILABLE, 'DE')->willReturn(true);
        $validator->validateFormat(self::VAT_VALID)->willReturn(true);
        $validator->validateCountry(self::VAT_VALID, 'DE')->willReturn(true);

        $validator->validate(self::VAT_VALID_FORMAT)->willReturn(false);
        $validator->validate(self::VAT_VALID_SERVICE_UNAVAILABLE)->willThrow(ClientException::class);
        $validator->validate(self::VAT_VALID)->willReturn(true);

        $this->beConstructedWith($validatorProvider, true, true, true);

        $this->validator = $validator;
    }

    function it_is_constraint_validator()
    {
        $this->shouldHaveType(ConstraintValidator::class);
    }

    function it_should_only_validate_vat_number_constraint(VatNumberAddressInterface $address, NotBlank $constraint)
    {
        $this->shouldThrow(UnexpectedTypeException::class)
            ->during('validate', [$address, $constraint]);
    }

    function it_should_only_validate_vat_number_address_interface(VatNumber $constraint)
    {
        $this->shouldThrow(UnexpectedValueException::class)
            ->during('validate', [self::VAT_VALID, $constraint]);
    }

    function it_should_not_validate_blank(VatNumberAddressInterface $address, VatNumber $constraint)
    {
        $address->getCountryCode()->willReturn('DE');

        $address->getVatNumber()->willReturn('');
        $this->validate($address, $constraint);

        $address->getVatNumber()->willReturn(null);
        $this->validate($address, $constraint);

        $this->validator->validateFormat(Argument::any())->shouldNotHaveBeenCalled();
    }

    function it_should_not_validate_without_country(VatNumberAddressInterface $address, VatNumber $constraint)
    {
        $address->getVatNumber()->willReturn(self::VAT_VALID);

        $address->getCountryCode()->willReturn('');
        $this->validate($address, $constraint);

        $address->getCountryCode()->willReturn(null);
        $this->validate($address, $constraint);

        $this->validator->validateFormat(Argument::any())->shouldNotHaveBeenCalled();
    }

    function it_should_not_validate_without_validator(VatNumberAddressInterface $address, VatNumber $constraint)
    {
        $address->getVatNumber()->willReturn(self::VAT_VALID);
        $address->getCountryCode()->willReturn('XY');

        $this->validate($address, $constraint);

        $this->validator->validateFormat(Argument::any())->shouldNotHaveBeenCalled();
    }

    function it_should_not_validate_if_not_active(
        VatNumberAddressInterface $address,
        VatNumber $constraint,
        VatNumberValidatorInterface $validator,
        VatNumberValidatorProviderInterface $validatorProvider
    ) {
        $this->setupValidator($validator, $validatorProvider, false, true, true);

        $address->getVatNumber()->willReturn(self::VAT_VALID);

        $this->validate($address, $constraint);
    }

    function it_should_validate_vat_format_only(
        VatNumberAddressInterface $address,
        VatNumber $constraint,
        VatNumberValidatorInterface $validator,
        VatNumberValidatorProviderInterface $validatorProvider
    ) {
        $this->setupValidator($validator, $validatorProvider, true, false, false);

        $address->getCountryCode()->willReturn('DE');
        $address->getVatNumber()->willReturn(self::VAT_VALID);

        $this->validate($address, $constraint);
    }

    function it_should_validate_vat_for_any_country(
        VatNumberAddressInterface $address,
        VatNumber $constraint,
        VatNumberValidatorInterface $validator,
        VatNumberValidatorProviderInterface $validatorProvider
    ) {
        $this->setupValidator($validator, $validatorProvider, true, false, true);

        $address->getCountryCode()->willReturn('AT');
        $address->getVatNumber()->willReturn(self::VAT_VALID);

        $this->validate($address, $constraint);

        $address->setVatValid(true)->shouldHaveBeenCalled();
    }

    function it_should_validate_vat_without_check_existence(
        VatNumberAddressInterface $address,
        VatNumber $constraint,
        VatNumberValidatorInterface $validator,
        VatNumberValidatorProviderInterface $validatorProvider
    ) {
        $this->setupValidator($validator, $validatorProvider, true, true, false);

        $address->getCountryCode()->willReturn('DE');
        $address->getVatNumber()->willReturn(self::VAT_VALID);

        $this->validate($address, $constraint);
    }

    function it_should_not_validate_wrong_vat_format(
        VatNumberAddressInterface $address,
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $violationBuilder,
        VatNumber $constraint
    ) {
        $this->initializeContextViolation(
            $context,
            $violationBuilder,
            'gewebe_sylius_vat_plugin.address.vat_number.invalid_format'
        );

        $address->getCountryCode()->willReturn('DE');
        $address->getVatNumber()->willReturn(self::VAT_INVALID);

        $this->validate($address, $constraint);
    }

    function it_should_not_validate_different_country(
        VatNumberAddressInterface $address,
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $violationBuilder,
        VatNumber $constraint
    ) {
        $this->initializeContextViolation(
            $context,
            $violationBuilder,
            'gewebe_sylius_vat_plugin.address.vat_number.invalid_country'
        );

        $address->getCountryCode()->willReturn('DE');
        $address->getVatNumber()->willReturn(self::VAT_INVALID_COUNTRY);

        $this->validate($address, $constraint);
    }

    function it_should_not_validate_existence(
        VatNumberAddressInterface $address,
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $violationBuilder,
        VatNumber $constraint
    ) {
        $this->initializeContextViolation(
            $context,
            $violationBuilder,
            'gewebe_sylius_vat_plugin.address.vat_number.not_verified'
        );

        $address->getCountryCode()->willReturn('DE');
        $address->getVatNumber()->willReturn(self::VAT_VALID_FORMAT);
        $address->setVatValid(false)->shouldBeCalled();

        $this->validate($address, $constraint);
    }

    function it_should_validate_complete(VatNumberAddressInterface $address, VatNumber $constraint)
    {
        $address->getCountryCode()->willReturn('DE');
        $address->getVatNumber()->willReturn(self::VAT_VALID);

        $this->validate($address, $constraint);

        $address->setVatValid(true)->shouldHaveBeenCalled();
    }

    function it_should_validate_if_vat_service_not_available(
        VatNumberAddressInterface $address,
        VatNumber $constraint
    ) {
        $address->getCountryCode()->willReturn('DE');
        $address->getVatNumber()->willReturn(self::VAT_VALID_SERVICE_UNAVAILABLE);

        $this->validate($address, $constraint);

        $address->setVatValid(Argument::any())->shouldNotHaveBeenCalled();
    }

    private function initializeContextViolation(
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $violationBuilder,
        string $messageId
    ) {
        $context->buildViolation($messageId)->willReturn($violationBuilder);

        $violationBuilder->atPath('vatNumber')->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->initialize($context);
    }

    private function setupValidator(
        VatNumberValidatorInterface $validator,
        VatNumberValidatorProviderInterface $validatorProvider,
        bool $isActive,
        bool $validateCountry,
        bool $validateExistence
    ) {
        $validatorProvider->getValidator(Argument::any())->willReturn($validator);

        $this->beConstructedWith($validatorProvider, $isActive, $validateCountry, $validateExistence);

        if ($isActive) {
            $validator->validateFormat(self::VAT_VALID)->shouldBeCalled();
        } else {
            $validator->validateFormat(self::VAT_VALID)->shouldNotBeCalled();
        }

        if ($isActive && $validateCountry) {
            $validator->validateCountry(self::VAT_VALID, Argument::any())->shouldBeCalled();
        } else {
            $validator->validateCountry(self::VAT_VALID, Argument::any())->shouldNotBeCalled();
        }

        if ($isActive && $validateExistence) {
            $validator->validate(self::VAT_VALID)->shouldBeCalled();
        } else {
            $validator->validate(self::VAT_VALID)->shouldNotBeCalled();
        }
    }
}
