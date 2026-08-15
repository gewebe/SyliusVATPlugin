<?php

declare(strict_types=1);

namespace Tests\Gewebe\SyliusVATPlugin\Unit\Vat\Number\Validator;

use Gewebe\SyliusVATPlugin\Vat\Number\ClientException;
use Gewebe\SyliusVATPlugin\Vat\Number\Hmrc\HmrcClientInterface;
use Gewebe\SyliusVATPlugin\Vat\Number\Validator\UkVatNumberValidator;
use Gewebe\SyliusVATPlugin\Vat\Number\VatNumberValidatorInterface;
use Ibericode\Vat\Validator;
use Ibericode\Vat\Vies\ViesException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class UkVatNumberValidatorTest extends TestCase
{
    private HmrcClientInterface&MockObject $client;

    private Validator&MockObject $viesValidator;

    private UkVatNumberValidator $ukVatNumberValidator;

    protected function setUp(): void
    {
        $this->client = $this->createMock(HmrcClientInterface::class);

        $this->client->method('checkVatNumber')
            ->willReturnCallback(function (string $vatRegistrationNumber): bool {
                if ($vatRegistrationNumber === '999999999') {
                    throw new ClientException('HMRC down');
                }

                return $vatRegistrationNumber === '123456789';
            });

        $this->viesValidator = $this->createMock(Validator::class);
        $this->viesValidator->method('validateVatNumberFormat')
            ->willReturnCallback(fn (string $vatNumber): bool => in_array($vatNumber, ['XI123456789', 'XI123456789000'], true));
        $this->viesValidator->method('validateVatNumber')
            ->willReturnCallback(function (string $vatNumber): bool {
                if ($vatNumber === 'XI999999999') {
                    throw new ViesException('VIES down');
                }

                return $vatNumber === 'XI123456789';
            });

        $this->ukVatNumberValidator = new UkVatNumberValidator($this->client, $this->viesValidator);
    }

    public function testIsVatNumberValidator(): void
    {
        self::assertInstanceOf(VatNumberValidatorInterface::class, $this->ukVatNumberValidator);
    }

    public function testGetCountries(): void
    {
        self::assertSame(['GB'], $this->ukVatNumberValidator->getCountries());
    }

    public function testValidateCountry(): void
    {
        self::assertTrue($this->ukVatNumberValidator->validateCountry('GB123456789', 'GB'));
        self::assertTrue($this->ukVatNumberValidator->validateCountry('GB123456789', 'gb'));
        self::assertTrue($this->ukVatNumberValidator->validateCountry('123456789', 'GB'));
        self::assertTrue($this->ukVatNumberValidator->validateCountry('GD001', 'GB'));
        self::assertTrue($this->ukVatNumberValidator->validateCountry('XI123456789', 'GB'));
        self::assertTrue($this->ukVatNumberValidator->validateCountry('xi 123 456 789', 'gb'));
        self::assertFalse($this->ukVatNumberValidator->validateCountry('GB123456789', 'IM'));
        self::assertFalse($this->ukVatNumberValidator->validateCountry('XI123456789', 'IE'));
        self::assertFalse($this->ukVatNumberValidator->validateCountry('DE123123123', 'GB'));
    }

    public function testValidateFormat(): void
    {
        self::assertTrue($this->ukVatNumberValidator->validateFormat('GB123456789'));
        self::assertTrue($this->ukVatNumberValidator->validateFormat('gb 123 4567 89'));
        self::assertTrue($this->ukVatNumberValidator->validateFormat('123456789'));
        self::assertTrue($this->ukVatNumberValidator->validateFormat('GB123456789001'));
        self::assertTrue($this->ukVatNumberValidator->validateFormat('GBGD001'));
        self::assertTrue($this->ukVatNumberValidator->validateFormat('GBHA599'));
        self::assertTrue($this->ukVatNumberValidator->validateFormat('XI123456789'));
        self::assertTrue($this->ukVatNumberValidator->validateFormat('xi 123 456 789 000'));
        self::assertFalse($this->ukVatNumberValidator->validateFormat('XI666XY'));
        self::assertFalse($this->ukVatNumberValidator->validateFormat('GB12345678'));
        self::assertFalse($this->ukVatNumberValidator->validateFormat('GBGD501'));
        self::assertFalse($this->ukVatNumberValidator->validateFormat('GBHA499'));
        self::assertFalse($this->ukVatNumberValidator->validateFormat('GB666XY'));
    }

    public function testValidate(): void
    {
        self::assertTrue($this->ukVatNumberValidator->validate('GB123456789'));
        self::assertTrue($this->ukVatNumberValidator->validate('GB 123 4567 89'));
        self::assertTrue($this->ukVatNumberValidator->validate('123456789'));
        self::assertTrue($this->ukVatNumberValidator->validate('GB123456789001'));
        self::assertTrue($this->ukVatNumberValidator->validate('XI123456789'));
        self::assertFalse($this->ukVatNumberValidator->validate('XI666666666'));
        self::assertFalse($this->ukVatNumberValidator->validate('GB666666666'));
    }

    public function testValidateWithInvalidFormat(): void
    {
        self::assertFalse($this->ukVatNumberValidator->validate('GB666XY'));
    }

    public function testValidateGovernmentDepartmentWithoutOnlineCheck(): void
    {
        $client = $this->createMock(HmrcClientInterface::class);
        $client->expects(self::never())->method('checkVatNumber');

        self::assertTrue((new UkVatNumberValidator($client, $this->viesValidator))->validate('GBGD001'));
    }

    public function testExceptionIfServiceUnavailable(): void
    {
        $this->expectException(ClientException::class);
        $this->ukVatNumberValidator->validate('GB999999999');
    }

    public function testExceptionIfViesServiceUnavailable(): void
    {
        $this->expectException(ClientException::class);
        $this->ukVatNumberValidator->validate('XI999999999');
    }
}
