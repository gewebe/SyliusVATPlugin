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

        $this->beConstructedWith(
            $validatorProvider,
            $isActive = true,
            $validateCountry = true,
            $validateExistence = true,
            $isCompanyVatRequired = true,
            $requiredCountries = ['IT'],
        );

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

    function it_should_not_validate_blank(
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $violationBuilder,
        VatNumberAddressInterface $address,
        VatNumber $constraint,
    ) {
        $context->buildViolation(Argument::any())->willReturn($violationBuilder);
        $violationBuilder->atPath(Argument::any())->willReturn($violationBuilder);
        $this->initialize($context);

        $this->validateAddress($address, $constraint, null, 'DE');

        $this->validateAddress($address, $constraint, null, 'DE', '');

        $this->validateAddress($address, $constraint, '', 'DE', '');

        $violationBuilder->addViolation()->shouldNotHaveBeenCalled();
        $this->validator->validateFormat(Argument::any())->shouldNotHaveBeenCalled();
    }

    function it_should_not_validate_without_country_validator(VatNumberAddressInterface $address, VatNumber $constraint)
    {
        $this->validateAddress($address, $constraint, self::VAT_VALID, '');

        $this->validateAddress($address, $constraint, self::VAT_VALID, null);

        $this->validateAddress($address, $constraint, self::VAT_VALID, 'XY');

        $this->validator->validateFormat(Argument::any())->shouldNotHaveBeenCalled();
    }

    function it_should_not_validate_if_not_active(
        VatNumberAddressInterface $address,
        VatNumber $constraint,
        VatNumberValidatorInterface $validator,
        VatNumberValidatorProviderInterface $validatorProvider
    ) {
        $this->initializeValidator($validator, $validatorProvider, false, true, true);

        $this->validateAddress($address, $constraint, self::VAT_VALID, 'DE');

        $validator->validateFormat(self::VAT_VALID)->shouldNotBeCalled();
    }

    function it_should_validate_vat_format_only(
        VatNumberAddressInterface $address,
        VatNumber $constraint,
        VatNumberValidatorInterface $validator,
        VatNumberValidatorProviderInterface $validatorProvider
    ) {
        $this->initializeValidator($validator, $validatorProvider, true, false, false);

        $this->validateAddress($address, $constraint, self::VAT_VALID, 'DE');

        $validator->validateFormat(self::VAT_VALID)->shouldBeCalled();
        $validator->validateCountry(self::VAT_VALID, Argument::any())->shouldNotBeCalled();
    }

    function it_should_validate_vat_for_any_country(
        VatNumberAddressInterface $address,
        VatNumber $constraint,
        VatNumberValidatorInterface $validator,
        VatNumberValidatorProviderInterface $validatorProvider
    ) {
        $this->initializeValidator($validator, $validatorProvider, true, false, true);

        $this->validateAddress($address, $constraint, self::VAT_VALID, 'AT');

        $address->setVatValid(true)->shouldHaveBeenCalled();

        $validator->validateFormat(self::VAT_VALID)->shouldBeCalled();
        $validator->validateCountry(self::VAT_VALID, Argument::any())->shouldNotBeCalled();
        $validator->validate(self::VAT_VALID)->shouldBeCalled();
    }

    function it_should_validate_vat_for_matching_country(
        VatNumberAddressInterface $address,
        VatNumber $constraint,
        VatNumberValidatorInterface $validator,
        VatNumberValidatorProviderInterface $validatorProvider
    ) {
        $this->initializeValidator($validator, $validatorProvider, true, true, false);

        $this->validateAddress($address, $constraint, self::VAT_VALID, 'DE');

        $validator->validateFormat(self::VAT_VALID)->shouldBeCalled();
        $validator->validateCountry(self::VAT_VALID, 'DE')->shouldBeCalled();
        $validator->validate(self::VAT_VALID)->shouldNotBeCalled();
    }

    function it_should_has_violation_when_required_for_company(
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $violationBuilder,
        VatNumberAddressInterface $address,
        VatNumber $constraint,
    ) {
        $this->initializeContextViolation(
            $context,
            $violationBuilder,
            'Required'
        );

        $constraint->messageRequiredForCompany = 'Required';

        $this->validateAddress($address, $constraint, null, 'DE', 'Test');

        $violationBuilder->addViolation()->shouldHaveBeenCalled();
    }

    function it_should_has_violation_when_required_for_country(
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $violationBuilder,
        VatNumberAddressInterface $address,
        VatNumber $constraint,
    ) {
        $this->initializeContextViolation(
            $context,
            $violationBuilder,
            'Required'
        );

        $constraint->messageRequired = 'Required';

        $this->validateAddress($address, $constraint, null, 'IT');

        $violationBuilder->addViolation()->shouldHaveBeenCalled();
    }

    function it_should_has_violation_for_invalid_format(
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

        $this->validateAddress($address, $constraint, self::VAT_INVALID, 'DE');
    }

    function it_should_has_violation_for_invalid_country(
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

        $this->validateAddress($address, $constraint, self::VAT_INVALID_COUNTRY, 'DE');
    }

    function it_should_has_violation_for_not_verified(
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

        $this->validateAddress($address, $constraint, self::VAT_VALID_FORMAT, 'DE');

        $address->setVatValid(false)->shouldBeCalled();
    }

    function it_should_validate_complete(VatNumberAddressInterface $address, VatNumber $constraint)
    {
        $this->validateAddress($address, $constraint, self::VAT_VALID, 'DE');

        $address->setVatValid(true)->shouldHaveBeenCalled();
    }

    function it_should_not_validate_if_vat_service_unavailable(
        VatNumberAddressInterface $address,
        VatNumber $constraint
    ) {
        $this->validateAddress($address, $constraint, self::VAT_VALID_SERVICE_UNAVAILABLE, 'DE');

        $address->setVatValid(Argument::any())->shouldNotHaveBeenCalled();
    }

    private function validateAddress(
        VatNumberAddressInterface $address,
        VatNumber $constraint,
        string|null $vatNumber,
        string|null $countryCode,
        string|null $company = null
    ) {
        $address->getCompany()->willReturn($company);
        $address->getCountryCode()->willReturn($countryCode);
        $address->getVatNumber()->willReturn($vatNumber);
        $address->hasVatNumber()->willReturn($vatNumber !== '' && $vatNumber !== null);

        $this->validate($address, $constraint);
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

    private function initializeValidator(
        VatNumberValidatorInterface $validator,
        VatNumberValidatorProviderInterface $validatorProvider,
        bool $isActive,
        bool $validateCountry,
        bool $validateExistence
    ) {
        $validatorProvider->getValidator(Argument::any())->willReturn($validator);

        $this->beConstructedWith($validatorProvider, $isActive, $validateCountry, $validateExistence);
    }
}
