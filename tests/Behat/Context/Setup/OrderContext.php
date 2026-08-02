<?php

declare(strict_types=1);

namespace Tests\Gewebe\SyliusVATPlugin\Behat\Context\Setup;

use Behat\Behat\Context\Context;
use Behat\Step\Given;
use Doctrine\Persistence\ObjectManager;
use Gewebe\SyliusVATPlugin\Entity\VatNumberAddressInterface;
use Sylius\Abstraction\StateMachine\StateMachineInterface;
use Sylius\Behat\Service\SharedStorageInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\OrderCheckoutTransitions;

class OrderContext implements Context
{
    public function __construct(
        private readonly ObjectManager $objectManager,
        private readonly SharedStorageInterface $sharedStorage,
        private readonly StateMachineInterface $stateMachine,
    ) {
    }

    #[Given('/^the customer set the (billing|shipping) address VAT number to "([^"]+)" which is (valid|invalid|unverified)$/')]
    public function theCustomerSetBillingAddressVatNumberTo(
        string $addressType,
        string $vatNumber,
        string $vatValidate,
    ): void {
        /** @var OrderInterface $order */
        $order = $this->sharedStorage->get('order');

        /** @var VatNumberAddressInterface $address */
        $address = $addressType === 'billing' ? $order->getBillingAddress() : $order->getShippingAddress();

        $address->setVatNumber($vatNumber);
        if ($vatValidate !== 'unverified') {
            $address->setVatValid($vatValidate === 'valid');
        }

        $this->objectManager->flush();

        $this->applyTransitionOnOrderCheckout($order, OrderCheckoutTransitions::TRANSITION_ADDRESS);
    }

    private function applyTransitionOnOrderCheckout(OrderInterface $order, string $transition): void
    {
        $this->stateMachine->apply($order, OrderCheckoutTransitions::GRAPH, $transition);
    }
}
