<?php

declare(strict_types=1);

namespace Tests\Gewebe\SyliusVATPlugin\Unit\Validator\Constraints;

use Gewebe\SyliusVATPlugin\Validator\Constraints\VatNumber;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraint;

final class VatNumberTest extends TestCase
{
    public function testIsConstraint(): void
    {
        $constraint = new VatNumber();
        self::assertInstanceOf(Constraint::class, $constraint);
    }

    public function testGetTargets(): void
    {
        $constraint = new VatNumber();
        self::assertSame(['class', 'property'], $constraint->getTargets());
    }
}
