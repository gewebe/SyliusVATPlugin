<?php

declare(strict_types=1);

namespace Gewebe\SyliusVATPlugin\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\Address as BaseAddress;

/**
 * Example address entity with vat number implemented as trait
 *
 * #@ORM\Entity
 * #@ORM\Table(name="sylius_address")
 */
class Address extends BaseAddress implements VatNumberAddressInterface
{
    use VatNumberAwareTrait;
}
