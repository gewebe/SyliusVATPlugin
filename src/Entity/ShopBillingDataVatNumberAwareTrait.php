<?php

declare(strict_types=1);

namespace Gewebe\SyliusVATPlugin\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use TestApplication\src\Entity\Channel\ShopBillingData;

/**
 * Trait that implements the shop billing data vat number functionality
 * Used in:
 * <li>@see ShopBillingData</li>
 */
trait ShopBillingDataVatNumberAwareTrait
{
    /**
     * @ORM\Column(name="vat_number", type="string", nullable=true)
     *
     * @Groups({"admin:shop_billing_data:read"})
     */
    #[ORM\Column(name: 'vat_number', type: Types::STRING, nullable: true)]
    #[Groups(['admin:shop_billing_data:read'])]
    protected ?string $vatNumber = null;

    public function getVatNumber(): ?string
    {
        return $this->vatNumber;
    }

    public function setVatNumber(?string $vatNumber): void
    {
        $this->vatNumber = $vatNumber;
    }

    public function hasVatNumber(): bool
    {
        return is_string($this->vatNumber) && strlen($this->vatNumber) > 0;
    }
}
