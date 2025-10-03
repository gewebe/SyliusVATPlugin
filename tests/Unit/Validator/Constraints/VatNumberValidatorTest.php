<?php

declare(strict_types=1);

namespace Tests\Gewebe\SyliusVATPlugin\Unit\Validator\Constraints;

use Gewebe\SyliusVATPlugin\Entity\VatNumberAddressInterface;
use Gewebe\SyliusVATPlugin\Validator\Constraints\VatNumber;
use Gewebe\SyliusVATPlugin\Validator\Constraints\VatNumberValidator;
use Gewebe\SyliusVATPlugin\Vat\Number\ClientException;
use Gewebe\SyliusVATPlugin\Vat\Number\VatNumberValidatorInterface;
use Gewebe\SyliusVATPlugin\Vat\Number\VatNumberValidatorProviderInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraints\Iban;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

final class VatNumberValidatorTest extends TestCase
{
    private const VAT_VALID = 'DE118716043';
    private const VAT_INVALID = 'XY123';
    private const VAT_INVALID_COUNTRY = 'ATU12345678';
    private const VAT_INVALID_REGISTRATION = 'DE123456789';
    private const VAT_SERVICE_UNAVAILABLE = 'DE123123123';

    private MockObject&VatNumberValidatorProviderInterface $provider;

    protected function setUp(): void
    {
        $vatNumberValidator = $this->createMock(VatNumberValidatorInterface::class);

        $vatNumberValidator->method('validateFormat')
            ->willReturnMap([
                                [self::VAT_VALID, true],
                                [self::VAT_INVALID, false],
                                [self::VAT_INVALID_COUNTRY, true],
                                [self::VAT_INVALID_REGISTRATION, true],
                                [self::VAT_SERVICE_UNAVAILABLE, true],
                            ]);

        $vatNumberValidator->method('validateCountry')
            ->willReturnMap([
                                [self::VAT_VALID, 'DE', true],
                                [self::VAT_INVALID_COUNTRY, 'DE', false],
                                [self::VAT_INVALID_REGISTRATION, 'DE', true],
                                [self::VAT_SERVICE_UNAVAILABLE, 'DE', true],
                            ]);

        $vatNumberValidator->method('validate')
            ->willReturnCallback(function (string $vatNumber) {
                if ($vatNumber == self::VAT_SERVICE_UNAVAILABLE) {
                    throw new ClientException();
                }

                return match ($vatNumber) {
                    self::VAT_VALID => true,
                    self::VAT_INVALID_REGISTRATION => false,
                };
            });

        $this->provider = $this->createMock(VatNumberValidatorProviderInterface::class);
        $this->provider->method('getValidator')
            ->willReturnCallback(function (string $countryCode) use ($vatNumberValidator) {
                return match ($countryCode) {
                    'DE' => $vatNumberValidator,
                    default => null,
                };
            });
    }

    public function testIsConstraintValidator(): void
    {
        self::assertInstanceOf(ConstraintValidator::class, $this->initVatNumberValidator());
    }

    public function testOnlyValidateVatNumberConstraint(): void
    {
        $this->expectException(UnexpectedTypeException::class);

        $this->initVatNumberValidator()->validate($this->createMock(VatNumberAddressInterface::class), new Iban());
    }

    public function testOnlyValidateVatNumberAddressInterface(): void
    {
        $this->expectException(UnexpectedValueException::class);

        $this->initVatNumberValidator()->validate('not an address', new VatNumber());
    }

    public function testViolationIfRequiredForCompanyEnabled(): void
    {
        $context = $this->getContext(true, 'messageRequiredForCompany');

        $this->validateAddress($context, null, 'DE', 'Sylius',);
    }

    public function testWithoutViolationIfRequiredForCompanyDisabled(): void
    {
        $context = $this->getContext(false, 'messageRequiredForCompany');

        $this->validateAddress($context, null, 'DE', 'Sylius', true, false);
        $this->validateAddress($context, '', 'DE', 'Sylius', true, false);
    }

    public function testViolationIfRequiredForCountryEnabled(): void
    {
        $this->validateAddress($this->getContext(true), null, 'IT');
    }

    public function testWithoutViolationIfNotActive(): void
    {
        $this->validateAddress($this->getContext(), self::VAT_INVALID, 'DE', null, false);
    }

    public function testWithoutViolationIfNoValidatorAvailable(): void
    {
        $this->validateAddress($this->getContext(), self::VAT_INVALID, 'XY');
    }

    public function testViolationForInvalidFormat(): void
    {
        $context = $this->getContext(true, 'messageFormat');

        $this->validateAddress($context, self::VAT_INVALID, 'DE');
    }

    public function testViolationForInvalidCountry(): void
    {
        $context = $this->getContext(true, 'messageCountry');

        $this->validateAddress($context, self::VAT_INVALID_COUNTRY, 'DE');
    }

    public function testViolationForInvalidRegistration(): void
    {
        $context = $this->getContext(true, 'messageVerified');

        $this->validateAddress($context, self::VAT_INVALID_REGISTRATION, 'DE');
    }

    public function testExceptionIfServiceUnavailable(): void
    {
        $this->validateAddress($this->getContext(), self::VAT_SERVICE_UNAVAILABLE, 'DE');
    }

    public function testValidVatNumber(): void
    {
        $this->validateAddress($this->getContext(), self::VAT_VALID, 'DE');
    }

    private function getContext(
        $expectsViolation = false,
        $expectsMessage = 'messageRequired'
    ): ExecutionContextInterface {
        $constraint = new VatNumber();

        $context = $this->createMock(ExecutionContextInterface::class);

        $violationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);
        $violationBuilder->method('atPath')->willReturn($violationBuilder);

        if ($expectsViolation === true) {
            $context->expects(self::once())
                ->method('buildViolation')
                ->with($constraint->$expectsMessage)
                ->willReturn($violationBuilder);

            $violationBuilder->expects(self::once())->method('addViolation');
        } else {
            $context->expects(self::never())
                ->method('buildViolation')
                ->with($constraint->$expectsMessage)
                ->willReturn($violationBuilder);

            $violationBuilder->expects(self::never())->method('addViolation');
        }

        return $context;
    }

    private function initVatNumberValidator(
        ?ExecutionContextInterface $context = null,
        bool $isActive = true,
        bool $validateCountry = true,
        bool $validateRegistration = true,
        bool $isCompanyVatRequired = true,
    ): VatNumberValidator {
        $vatNumberValidator = new VatNumberValidator(
            $this->provider,
            $isActive,
            $validateCountry,
            $validateRegistration,
            $isCompanyVatRequired,
            ['IT'],
        );

        if ($context !== null) {
            $vatNumberValidator->initialize($context);
        }

        return $vatNumberValidator;
    }

    private function validateAddress(
        ?ExecutionContextInterface $context,
        ?string $vatNumber,
        ?string $countryCode,
        ?string $company = null,
        bool $isActive = true,
        bool $isCompanyVatRequired = true,
    ): void {
        $vatNumberValidator = $this->initVatNumberValidator(
            $context,
            $isActive,
            true,
            true,
            $isCompanyVatRequired,
        );

        $address = $this->createMock(VatNumberAddressInterface::class);
        $address->method('getCompany')->willReturn($company);
        $address->method('getCountryCode')->willReturn($countryCode);
        $address->method('getVatNumber')->willReturn($vatNumber);
        $address->method('hasVatNumber')->willReturn($vatNumber !== '' && $vatNumber !== null);

        if ($vatNumber === self::VAT_VALID) {
            $address->expects(self::once())->method('setVatValid')->with(true);
        } elseif($vatNumber === self::VAT_INVALID_REGISTRATION) {
            $address->expects(self::once())->method('setVatValid')->with(false);
        } else {
            $address->expects(self::never())->method('setVatValid');
        }

        $vatNumberValidator->validate($address, new VatNumber());
    }
}
