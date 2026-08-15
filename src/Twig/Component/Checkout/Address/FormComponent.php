<?php

declare(strict_types=1);

namespace Gewebe\SyliusVATPlugin\Twig\Component\Checkout\Address;

use Gewebe\SyliusVATPlugin\Entity\VatNumberAddressInterface;
use Sylius\Bundle\ShopBundle\Twig\Component\Checkout\Address\AddressBookComponent;
use Sylius\Bundle\ShopBundle\Twig\Component\Checkout\Address\FormComponent as BaseFormComponent;
use Sylius\Component\Core\Model\CustomerInterface;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveListener;

/**
 * Sylius 2.1 compatibility fallback; Sylius 2.2+ uses VatNumberAddressFormValuesModifier instead.
 */
final class FormComponent extends BaseFormComponent
{
    #[LiveListener(AddressBookComponent::SYLIUS_SHOP_ADDRESS_UPDATED)]
    public function addressFieldUpdated(#[LiveArg] mixed $addressId, #[LiveArg] string $field): void
    {
        $customer = $this->customerContext->getCustomer();
        if (!$customer instanceof CustomerInterface || !is_scalar($addressId)) {
            return;
        }

        $address = $this->addressRepository->findOneByCustomer((string) $addressId, $customer);
        if (!$address instanceof VatNumberAddressInterface) {
            return;
        }

        parent::addressFieldUpdated($addressId, $field);
        $this->formValues[$field]['vatNumber'] = $address->getVatNumber();
    }
}
