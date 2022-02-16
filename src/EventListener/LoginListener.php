<?php

declare(strict_types=1);

namespace Gewebe\SyliusVATPlugin\EventListener;

use Gewebe\SyliusVATPlugin\Entity\VatNumberAddressInterface;
use Gewebe\SyliusVATPlugin\Validator\Constraints\VatNumber;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\ShopUserInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class LoginListener
{
    public function __construct(
        private ValidatorInterface $validator,
        private bool $revalidateOnLogin,
        private int $expirationDays
    ) {
    }

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event): void
    {
        if ($this->revalidateOnLogin === false) {
            return;
        }

        $user = $event->getAuthenticationToken()->getUser();
        if (!$user instanceof ShopUserInterface) {
            return;
        }

        $customer = $user->getCustomer();
        if (!$customer instanceof CustomerInterface) {
            return;
        }

        $address = $customer->getDefaultAddress();
        if (!$address instanceof VatNumberAddressInterface) {
            return;
        }

        if (!$address->hasVatNumber()) {
            return;
        }

        $revalidationDate = new \DateTime($this->expirationDays.' days ago');

        if (is_null($address->getVatValidatedAt()) || $address->getVatValidatedAt() < $revalidationDate) {
            $violations = $this->validator->validate($address, new VatNumber());

            if (0 !== count($violations)) {
                $address->setVatValid(false);
            }
        }
    }
}
