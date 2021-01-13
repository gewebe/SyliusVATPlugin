<?php

declare(strict_types=1);

namespace Gewebe\SyliusVATPlugin\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Trait that implements the vat number functionality
 * Used in:
 * <li>@see Address</li>
 */
trait VatNumberAwareTrait
{
    /**
     * @ORM\Column(name="vat_number", type="string", nullable=true)
     * @Gedmo\Versioned()
     *
     * @var string|null
     */
    protected $vatNumber;

    /**
     * @ORM\Column(name="vat_valid", type="boolean")
     *
     * @var bool
     */
    protected $vatValid = false;

    /**
     * @ORM\Column(name="vat_validated_at", type="datetime", nullable=true)
     *
     * @var DateTime|null
     */
    protected $vatValidatedAt;

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

    public function hasValidVatNumber(): bool
    {
        return ($this->hasVatNumber() && $this->vatValid === true);
    }

    public function setVatValid(bool $valid, ?DateTime $validatedAt = null): void
    {
        $this->vatValid = $valid;
        $this->vatValidatedAt = $validatedAt ?? new DateTime();
    }

    public function getVatValidatedAt(): ?DateTime
    {
        return $this->vatValidatedAt;
    }
}
