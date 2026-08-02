<?php

declare(strict_types=1);

namespace Tests\Gewebe\SyliusVATPlugin\Unit\Vat\Number\Validator;

use Gewebe\SyliusVATPlugin\Vat\Number\ClientException;
use Gewebe\SyliusVATPlugin\Vat\Number\Validator\EuVatNumberValidator;
use Gewebe\SyliusVATPlugin\Vat\Number\VatNumberValidatorInterface;
use Ibericode\Vat\Validator;
use Ibericode\Vat\Vies\ViesException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class EuVatNumberValidatorTest extends TestCase
{
    private Validator&MockObject $validator;
    private EuVatNumberValidator $euVatNumberValidator;

    protected function setUp(): void
    {
        $this->validator = $this->createMock(Validator::class);

        $this->validator->method('validateVatNumberFormat')
            ->willReturnCallback(function (string $vatNumber) {
                return match ($vatNumber) {
                    'DE666XY' => false,
                    'DE123456789' => true,
                    default => false,
                };
            });

        $this->validator->method('validateVatNumber')
            ->willReturnCallback(function (string $vatNumber) {
                if ($vatNumber === 'DE999999999') {
                    throw new ViesException('VIES down');
                }

                return $vatNumber === 'DE123456789';
            });

        $this->euVatNumberValidator = new EuVatNumberValidator($this->validator);
    }

    public function testIsVatNumberValidator(): void
    {
        self::assertInstanceOf(VatNumberValidatorInterface::class, $this->euVatNumberValidator);
    }

    public function testValidateCountry(): void
    {
        self::assertTrue($this->euVatNumberValidator->validateCountry('DE123456789', 'DE'));
        self::assertTrue($this->euVatNumberValidator->validateCountry('DE123456789', 'de'));
        self::assertFalse($this->euVatNumberValidator->validateCountry('DE123456789', 'FR'));
    }

    public function testValidateFormat(): void
    {
        self::assertFalse($this->euVatNumberValidator->validateFormat('DE666XY'));
        self::assertTrue($this->euVatNumberValidator->validateFormat('DE123456789'));
    }

    public function testValidate(): void
    {
        self::assertTrue($this->euVatNumberValidator->validate('DE123456789'));
    }

    public function testExceptionIfServiceUnavailable(): void
    {
        $this->expectException(ClientException::class);
        $this->euVatNumberValidator->validate('DE999999999');
    }
}
