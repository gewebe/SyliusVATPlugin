<?php

declare(strict_types=1);

namespace spec\Gweb\SyliusVATPlugin\Validator\Constraints;

use Gweb\SyliusVATPlugin\Entity\VatNumberAddressInterface;
use Gweb\SyliusVATPlugin\Validator\Constraints\VatNumber;
use Gweb\SyliusVATPlugin\Vat\Number\ClientException;
use Gweb\SyliusVATPlugin\Vat\Number\ValidatorInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class VatNumberValidatorSpec extends ObjectBehavior
{
    function let(ValidatorInterface $validator)
    {
        $validator->validateFormat('XY123')->willReturn(false);
        $validator->validateFormat('ATU99999999')->willReturn(true);
        $validator->validateCountry('ATU99999999', 'DE')->willReturn(false);
        $validator->validateFormat('DE999999999')->willReturn(true);
        $validator->validateCountry('DE999999999', 'DE')->willReturn(true);
        $validator->validateFormat('DE123123123')->willReturn(true);
        $validator->validateCountry('DE123123123', 'DE')->willReturn(true);
        $validator->validateFormat('DE118716043')->willReturn(true);
        $validator->validateCountry('DE118716043', 'DE')->willReturn(true);

        $validator->validate('DE999999999')->willReturn(false);
        $validator->validate('DE123123123')->willThrow(ClientException::class);
        $validator->validate('DE118716043')->willReturn(true);

        $this->beConstructedWith($validator, true, true, true);
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

    function it_should_not_validate_blank(VatNumberAddressInterface $address, VatNumber $constraint)
    {
        $address->getVatNumber()->willReturn('');
        $this->validate($address, $constraint)->shouldReturn(null);

        $address->getVatNumber()->willReturn(null);
        $this->validate($address, $constraint)->shouldReturn(null);
    }

    function it_should_validate_any_string(
        VatNumberAddressInterface $address,
        VatNumber $constraint,
        ValidatorInterface $validator
    ) {
        $this->beConstructedWith($validator, false, false, false);

        $address->getVatNumber()->willReturn('XY123');

        $this->validate($address, $constraint);
    }

    function it_should_validate_vat_format_only(
        VatNumberAddressInterface $address,
        VatNumber $constraint,
        ValidatorInterface $validator
    ) {
        $this->beConstructedWith($validator, true, false, false);

        $address->getVatNumber()->willReturn('DE999999999');

        $this->validate($address, $constraint);
    }

    function it_should_validate_vat_for_any_country(
        VatNumberAddressInterface $address,
        VatNumber $constraint,
        ValidatorInterface $validator
    ) {
        $this->beConstructedWith($validator, true, false, false);

        $address->getCountryCode()->willReturn('DE');
        $address->getVatNumber()->willReturn('ATU99999999');

        $this->validate($address, $constraint);
    }

    function it_should_validate_vat_for_any_country_and_check_existence(
        VatNumberAddressInterface $address,
        VatNumber $constraint,
        ValidatorInterface $validator
    ) {
        $this->beConstructedWith($validator, true, false, true);

        $address->getCountryCode()->willReturn('AT');
        $address->getVatNumber()->willReturn('DE118716043');
        $address->setVatValid(true)->shouldBeCalled();

        $this->validate($address, $constraint);
    }

    function it_should_validate_vat_without_check_existence(
        VatNumberAddressInterface $address,
        VatNumber $constraint,
        ValidatorInterface $validator
    ) {
        $this->beConstructedWith($validator, true, true, false);

        $address->getCountryCode()->willReturn('DE');
        $address->getVatNumber()->willReturn('DE999999999');

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
            'gweb_sylius_vat.address.vat_number.invalid_format'
        );

        $address->getVatNumber()->willReturn('XY123');

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
            'gweb_sylius_vat.address.vat_number.invalid_country'
        );

        $address->getCountryCode()->willReturn('DE');
        $address->getVatNumber()->willReturn('ATU99999999');

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
            'gweb_sylius_vat.address.vat_number.not_verified'
        );

        $address->getCountryCode()->willReturn('DE');
        $address->getVatNumber()->willReturn('DE999999999');
        $address->setVatValid(false)->shouldBeCalled();

        $this->validate($address, $constraint);
    }

    function it_should_validate_complete(VatNumberAddressInterface $address, VatNumber $constraint)
    {
        $address->getCountryCode()->willReturn('DE');
        $address->getVatNumber()->willReturn('DE118716043');
        $address->setVatValid(true)->shouldBeCalled();

        $this->validate($address, $constraint)->shouldReturn(null);
    }

    function it_should_validate_if_vat_service_not_available(VatNumberAddressInterface $address, VatNumber $constraint)
    {
        $address->getCountryCode()->willReturn('DE');
        $address->getVatNumber()->willReturn('DE123123123');

        $this->validate($address, $constraint)->shouldReturn(null);
    }

    private function initializeContextViolation(
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $violationBuilder,
        string $messageId
    ) {
        $context->buildViolation($messageId)
            ->willReturn($violationBuilder);
        $violationBuilder->atPath('vatNumber')
            ->willReturn($violationBuilder);
        $violationBuilder->addViolation()
            ->shouldBeCalled();

        $this->initialize($context);
    }
}
