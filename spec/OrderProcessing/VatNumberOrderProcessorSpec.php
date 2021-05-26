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
use Sylius\Component\Order\Processor\OrderProcessorInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

final class VatNumberOrderProcessorSpec extends ObjectBehavior
{
    function let(
        RepositoryInterface $zoneRepository,
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

        $this->beConstructedWith($zoneRepository, true);
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
        OrderInterface $order
    ): void {
        $this->beConstructedWith($zoneRepository, false);

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
        VatNumberAddressInterface $customerBillingAddress
    ) {
        $shopBillingData->getCountryCode()->willReturn('FR')->shouldBeCalled();

        $customerBillingAddress->hasValidVatNumber()->willReturn(true);
        $customerBillingAddress->getCountryCode()->willReturn('FR')->shouldBeCalled();

        $channel->getShopBillingData()->willReturn($shopBillingData);
        $order->getChannel()->willReturn($channel);

        $order->getBillingAddress()->willReturn($customerBillingAddress);

        $this->process($order);
    }

    function it_process_valid_vat(
        ChannelInterface $channel,
        OrderInterface $order,
        OrderItemInterface $orderItem,
        ShopBillingData $shopBillingData,
        VatNumberAddressInterface $customerBillingAddress
    ) {
        $shopBillingData->getCountryCode()->willReturn('DE');

        $customerBillingAddress->hasValidVatNumber()->willReturn(true);
        $customerBillingAddress->getCountryCode()->willReturn('FR');

        $channel->getShopBillingData()->willReturn($shopBillingData);
        $order->getChannel()->willReturn($channel);

        $order->getBillingAddress()->willReturn($customerBillingAddress);

        $order->getItems()->willReturn(new ArrayCollection([$orderItem->getWrappedObject()]));
        $orderItem->removeAdjustmentsRecursively(AdjustmentInterface::TAX_ADJUSTMENT)->shouldBeCalled();

        $this->process($order);
    }
}
