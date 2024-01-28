<?php

declare(strict_types=1);

namespace Tests\Gewebe\SyliusVATPlugin\Behat\Context\Setup;

use Behat\Behat\Context\Context;
use Doctrine\Persistence\ObjectManager;
use Gewebe\SyliusVATPlugin\Entity\VatNumberAddressInterface;
use SM\Factory\FactoryInterface as StateMachineFactoryInterface;
use Sylius\Behat\Service\SharedStorageInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\OrderCheckoutTransitions;

class OrderContext implements Context
{
    public function __construct(
        private ObjectManager $objectManager,
        private SharedStorageInterface $sharedStorage,
        private StateMachineFactoryInterface $stateMachineFactory,
    ) {
    }

    /**
     * @Given /^the customer set the (billing|shipping) address VAT number to "([^"]+)" which is (valid|invalid|unverified)$/
     */
    public function theCustomerSetBillingAddressVatNumberTo($addressType, $vatNumber, $vatValidate)
    {
        /** @var OrderInterface $order */
        $order = $this->sharedStorage->get('order');

        if ($addressType == 'billing') {
            /** @var VatNumberAddressInterface $address */
            $address = $order->getBillingAddress();
        } else {
            /** @var VatNumberAddressInterface $address */
            $address = $order->getShippingAddress();
        }
        $address->setVatNumber($vatNumber);
        if ($vatValidate !== 'unverified') {
            $address->setVatValid(($vatValidate === 'valid') ? true : false);
        }

        $this->objectManager->flush();

        $this->applyTransitionOnOrderCheckout($order, OrderCheckoutTransitions::TRANSITION_ADDRESS);
    }

    /**
     * @param string $transition
     */
    private function applyTransitionOnOrderCheckout(OrderInterface $order, $transition)
    {
        $this->stateMachineFactory->get($order, OrderCheckoutTransitions::GRAPH)->apply($transition);
    }
}
