<?php

declare(strict_types=1);

namespace spec\Gewebe\SyliusVATPlugin\Validator\Constraints;

use PhpSpec\ObjectBehavior;
use Symfony\Component\Validator\Constraint;

class VatNumberSpec extends ObjectBehavior
{
    function it_is_constraint()
    {
        $this->shouldHaveType(Constraint::class);
    }

    function it_has_targets()
    {
        $this->getTargets()->shouldReturn(['class', 'property']);
    }
}
