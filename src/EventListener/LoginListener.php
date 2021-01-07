<?php

declare(strict_types=1);

namespace Gweb\SyliusVATPlugin\EventListener;

use Gweb\SyliusVATPlugin\Entity\VatNumberAddressInterface;
use Gweb\SyliusVATPlugin\Validator\Constraints\VatNumber;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\ShopUserInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class LoginListener
{
    /** @var ValidatorInterface */
    private $validator;

    /** @var bool */
    private $revalidateOnLogin;

    /** @var int */
    private $expirationDays;

    public function __construct(
        ValidatorInterface $validator,
        bool $revalidateOnLogin,
        int $expirationDays
    ) {
        $this->validator = $validator;
        $this->revalidateOnLogin = $revalidateOnLogin;
        $this->expirationDays = $expirationDays;
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
