<?php

declare(strict_types=1);

namespace Tests\Gweb\SyliusVATPlugin\Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gweb\SyliusVATPlugin\Entity\VatNumberAddressInterface;
use Gweb\SyliusVATPlugin\Entity\VatNumberAwareTrait;
use Sylius\Component\Core\Model\Address as BaseAddress;

/**
 * Address entity with vat number
 *
 * @ORM\Entity
 * @ORM\Table(name="sylius_address")
 */
class Address extends BaseAddress implements VatNumberAddressInterface
{
    use VatNumberAwareTrait;
}
