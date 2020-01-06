<?php

declare(strict_types=1);

namespace Gweb\SyliusVATPlugin\Entity;

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
     * @ORM\Column(name="vat_valid", type="boolean", nullable=true, options={"unsigned":true, "default":0})
     *
     * @var bool|null
     */
    protected $vatValid;

    /**
     * @ORM\Column(name="vat_validated_at", type="datetime", nullable=true)
     *
     * @var DateTime|null
     */
    protected $vatValidatedAt;

    /** {@inheritdoc} */
    public function getVatNumber(): ?string
    {
        return $this->vatNumber;
    }

    /** {@inheritdoc} */
    public function setVatNumber(?string $vatNumber): void
    {
        $this->vatNumber = $vatNumber;
    }

    /** {@inheritdoc} */
    public function hasVatNumber(): bool
    {
        return !empty($this->getVatNumber());
    }

    /** {@inheritdoc} */
    public function hasValidVatNumber(): bool
    {
        return ($this->hasVatNumber() && !empty($this->vatValid));
    }

    /** {@inheritdoc} */
    public function setVatValid(bool $validated, ?DateTime $vatValidationDate = null): void
    {
        $this->vatValid = $validated;
        $this->vatValidatedAt = $vatValidationDate ?? new DateTime();
    }

    /** {@inheritdoc} */
    public function getVatValidatedAt(): ?DateTime
    {
        return $this->vatValidatedAt;
    }
}
