<?php

declare(strict_types=1);

namespace spec\Gewebe\SyliusVATPlugin\OrderProcessing;

use Doctrine\Common\Collections\ArrayCollection;
use Gewebe\SyliusVATPlugin\Entity\VatNumberAddressInterface;
use Gewebe\SyliusVATPlugin\OrderProcessing\VatNumberOrderProcessor;
use PhpSpec\ObjectBehavior;
use Sylius\Component\Addressing\Model\ZoneInterface;
use Sylius\Component\Addressing\Model\ZoneMemberInterface;
use Sylius\Component\Core\Model\AdjustmentInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Core\Model\Scope;
use Sylius\Component\Core\Model\ShopBillingData;
use Sylius\Component\Core\Resolver\TaxationAddressResolverInterface;
use Sylius\Component\Order\Processor\OrderProcessorInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

final class VatNumberOrderProcessorSpec extends ObjectBehavior
{
    function let(
        RepositoryInterface $zoneRepository,
        TaxationAddressResolverInterface $taxationAddressResolver,
        ZoneInterface $euZone,
        ZoneMemberInterface $de,
        ZoneMemberInterface $fr
    ) {
        $de->getCode()->willReturn('DE');
        $fr->getCode()->willReturn('FR');

        $euZone->getMembers()->willReturn(new ArrayCollection([
            $de->getWrappedObject(),
            $fr->getWrappedObject(),
        ]));

        $zoneRepository->findOneBy(['code' => 'EU', 'scope' => Scope::ALL])->willReturn($euZone);

        $this->beConstructedWith($zoneRepository, $taxationAddressResolver, true);
    }

    function it_is_vat_number_processor()
    {
        $this->shouldHaveType(VatNumberOrderProcessor::class);
    }

    function it_implements_order_processor_interface(): void
    {
        $this->shouldImplement(OrderProcessorInterface::class);
    }

    function it_does_not_process_deactivated(
        RepositoryInterface $zoneRepository,
        TaxationAddressResolverInterface $taxationAddressResolver,
        OrderInterface $order
    ): void {
        $this->beConstructedWith($zoneRepository, $taxationAddressResolver, false);

        $this->process($order);
    }

    function it_does_not_process_without_address(
        ChannelInterface $channel,
        OrderInterface $order
    ) {
        $order->getChannel()->willReturn($channel);

        $order->getBillingAddress()->willReturn(null);

        $this->process($order);
    }

    function it_does_not_process_invalid_vat(
        ChannelInterface $channel,
        OrderInterface $order,
        ShopBillingData $shopBillingData,
        VatNumberAddressInterface $customerBillingAddress
    ) {
        $customerBillingAddress->hasValidVatNumber()->willReturn(false);

        $channel->getShopBillingData()->willReturn($shopBillingData);
        $order->getChannel()->willReturn($channel);

        $order->getBillingAddress()->willReturn($customerBillingAddress);

        $this->process($order);
    }

    function it_does_not_process_same_country(
        ChannelInterface $channel,
        OrderInterface $order,
        ShopBillingData $shopBillingData,
        TaxationAddressResolverInterface $taxationAddressResolver,
        VatNumberAddressInterface $taxationAddress
    ) {
        $shopBillingData->getCountryCode()->willReturn('FR')->shouldBeCalled();

        $taxationAddress->hasValidVatNumber()->willReturn(true);
        $taxationAddress->getCountryCode()->willReturn('FR')->shouldBeCalled();
        $taxationAddressResolver->getTaxationAddressFromOrder($order)->willReturn($taxationAddress);

        $channel->getShopBillingData()->willReturn($shopBillingData);
        $order->getChannel()->willReturn($channel);

        $this->process($order);
    }

    function it_process_valid_vat(
        ChannelInterface $channel,
        OrderInterface $order,
        OrderItemInterface $orderItem,
        ShopBillingData $shopBillingData,
        TaxationAddressResolverInterface $taxationAddressResolver,
        VatNumberAddressInterface $taxationAddress,
        AdjustmentInterface $taxAdjustment,
        AdjustmentInterface $shippingAdjustment
    ) {
        $shopBillingData->getCountryCode()->willReturn('DE');
        $channel->getShopBillingData()->willReturn($shopBillingData);
        $order->getChannel()->willReturn($channel);

        $taxationAddress->hasValidVatNumber()->willReturn(true);
        $taxationAddress->getCountryCode()->willReturn('FR');
        $taxationAddressResolver->getTaxationAddressFromOrder($order)->willReturn($taxationAddress);

        $taxAdjustment->isNeutral()->willReturn(true);
        $taxAdjustment->getDetails()->willReturn(['shippingMethodCode' => 'Post']);
        $taxAdjustment->getAmount()->willReturn(5);
        $order->getAdjustments(AdjustmentInterface::TAX_ADJUSTMENT)->willReturn(new ArrayCollection([
            $taxAdjustment->getWrappedObject()
        ]));

        $shippingAdjustment->getDetails()->willReturn(['shippingMethodCode' => 'Post']);
        $shippingAdjustment->getAmount()->willReturn(25);
        $shippingAdjustment->setAmount(20)->shouldBeCalled();
        $order->getAdjustments(AdjustmentInterface::SHIPPING_ADJUSTMENT)->willReturn(new ArrayCollection([
            $shippingAdjustment->getWrappedObject(),
        ]));

        $orderItem->getQuantity()->willReturn(1);
        $orderItem->getUnitPrice()->willReturn(25);
        $orderItem->setUnitPrice(20)->shouldBeCalled();
        $orderItem->recalculateUnitsTotal()->shouldBeCalled();
        $orderItem->getAdjustmentsRecursively(AdjustmentInterface::TAX_ADJUSTMENT)->willReturn(new ArrayCollection([
            $taxAdjustment->getWrappedObject()
        ]));

        $order->getItems()->willReturn(new ArrayCollection([
            $orderItem->getWrappedObject(),
        ]));

        $order->removeAdjustmentsRecursively(AdjustmentInterface::TAX_ADJUSTMENT)->shouldBeCalled();

        $this->process($order);
    }
}
