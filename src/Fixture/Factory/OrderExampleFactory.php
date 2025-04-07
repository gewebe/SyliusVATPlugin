<?php

declare(strict_types=1);

namespace Gewebe\SyliusVATPlugin\Fixture\Factory;

use Doctrine\Persistence\ObjectManager;
use Gewebe\SyliusVATPlugin\Entity\VatNumberAddressInterface;
use SM\Factory\FactoryInterface as StateMachineFactoryInterface;
use Sylius\Abstraction\StateMachine\StateMachineInterface;
use Sylius\Bundle\CoreBundle\Fixture\Factory\OrderExampleFactory as BaseOrderExampleFactory;
use Sylius\Component\Addressing\Model\CountryInterface;
use Sylius\Component\Core\Checker\OrderPaymentMethodSelectionRequirementCheckerInterface;
use Sylius\Component\Core\Checker\OrderShippingMethodSelectionRequirementCheckerInterface;
use Sylius\Component\Core\Model\AddressInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Core\OrderCheckoutTransitions;
use Sylius\Component\Core\Repository\PaymentMethodRepositoryInterface;
use Sylius\Component\Core\Repository\ProductRepositoryInterface;
use Sylius\Component\Core\Repository\ShippingMethodRepositoryInterface;
use Sylius\Component\Order\Modifier\OrderItemQuantityModifierInterface;
use Sylius\Resource\Doctrine\Persistence\RepositoryInterface;
use Sylius\Resource\Factory\FactoryInterface;

final class OrderExampleFactory extends BaseOrderExampleFactory
{
    /**
     * @param FactoryInterface<OrderInterface> $orderFactory
     * @param FactoryInterface<OrderItemInterface> $orderItemFactory
     * @param RepositoryInterface<ChannelInterface> $channelRepository
     * @param RepositoryInterface<CustomerInterface> $customerRepository
     * @param RepositoryInterface<CountryInterface> $countryRepository
     * @param FactoryInterface<AddressInterface> $addressFactory
     */
    public function __construct(
        protected FactoryInterface $orderFactory,
        protected FactoryInterface $orderItemFactory,
        protected OrderItemQuantityModifierInterface $orderItemQuantityModifier,
        protected ObjectManager $orderManager,
        protected RepositoryInterface $channelRepository,
        protected RepositoryInterface $customerRepository,
        protected ProductRepositoryInterface $productRepository,
        protected RepositoryInterface $countryRepository,
        protected PaymentMethodRepositoryInterface $paymentMethodRepository,
        protected ShippingMethodRepositoryInterface $shippingMethodRepository,
        protected FactoryInterface $addressFactory,
        protected StateMachineFactoryInterface|StateMachineInterface $stateMachineFactory,
        protected OrderShippingMethodSelectionRequirementCheckerInterface $orderShippingMethodSelectionRequirementChecker,
        protected OrderPaymentMethodSelectionRequirementCheckerInterface $orderPaymentMethodSelectionRequirementChecker,
    ) {
        parent::__construct(
            $orderFactory,
            $orderItemFactory,
            $orderItemQuantityModifier,
            $orderManager,
            $channelRepository,
            $customerRepository,
            $productRepository,
            $countryRepository,
            $paymentMethodRepository,
            $shippingMethodRepository,
            $addressFactory,
            $stateMachineFactory,
            $orderShippingMethodSelectionRequirementChecker,
            $orderPaymentMethodSelectionRequirementChecker,
        );
    }

    protected function address(OrderInterface $order, string $countryCode): void
    {
        /** @var VatNumberAddressInterface $address */
        $address = $this->addressFactory->createNew();
        $address->setFirstName($this->faker->firstName());
        $address->setLastName($this->faker->lastName());
        $address->setStreet($this->faker->streetAddress());
        $address->setCountryCode($countryCode);
        $address->setCity($this->faker->city());
        $address->setPostcode($this->faker->postcode());
        $address->setVatValid(false);

        $order->setShippingAddress($address);
        $order->setBillingAddress(clone $address);

        $this->applyCheckoutStateTransition($order, OrderCheckoutTransitions::TRANSITION_ADDRESS);
    }
}
