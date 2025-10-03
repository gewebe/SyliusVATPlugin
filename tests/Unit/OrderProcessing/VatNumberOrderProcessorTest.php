<?php

declare(strict_types=1);

namespace Tests\Gewebe\SyliusVATPlugin\Unit\OrderProcessing;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Gewebe\SyliusVATPlugin\Entity\VatNumberAddressInterface;
use Gewebe\SyliusVATPlugin\OrderProcessing\VatNumberOrderProcessor;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Addressing\Model\ZoneInterface;
use Sylius\Component\Addressing\Model\ZoneMemberInterface;
use Sylius\Component\Addressing\Repository\ZoneRepositoryInterface;
use Sylius\Component\Core\Model\AdjustmentInterface;
use Sylius\Component\Core\Model\Channel;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Core\Model\Scope;
use Sylius\Component\Core\Model\ShopBillingDataInterface;
use Sylius\Component\Core\Resolver\TaxationAddressResolverInterface;
use Sylius\Component\Order\Processor\OrderProcessorInterface;

final class VatNumberOrderProcessorTest extends TestCase
{
    public function testIsOrderProcessor(): void
    {
        self::assertInstanceOf(OrderProcessorInterface::class, $this->initVatNumberOrderProcessor());
    }

    public function testDeactivated(): void
    {
        $order = $this->createOrder(null, 'getChannel');

        $this->processOrder($order, null, null, false);
    }

    public function testWithoutEuZone(): void
    {
        $zoneRepository = $this->createMock(ZoneRepositoryInterface::class);

        $order = $this->createOrder(null, 'getChannel');

        $this->processOrder($order, null, $zoneRepository);
    }

    public function testWithoutChannel(): void
    {
        $order = $this->createOrder();

        $this->processOrder($order);
    }

    public function testWithoutShopBillingData(): void
    {
        $channel = $this->createChannel();
        $order = $this->createOrder($channel);

        $this->processOrder($order);
    }

    public function testNoEuShopBillingData(): void
    {
        $shopBillingData = $this->createShopBillingData('CH');
        $channel = $this->createChannel($shopBillingData);
        $order = $this->createOrder($channel);

        $this->processOrder($order);
    }

    public function testNoEuTaxationAddress(): void
    {
        $taxationAddressResolver = $this->createTaxationAddressResolver('CH', true);

        $shopBillingData = $this->createShopBillingData('DE');
        $channel = $this->createChannel($shopBillingData);
        $order = $this->createOrder($channel);

        $this->processOrder($order, $taxationAddressResolver);
    }

    public function testNoValidVatNumber(): void
    {
        $taxationAddressResolver = $this->createTaxationAddressResolver('DE', false);

        $shopBillingData = $this->createShopBillingData('DE');
        $channel = $this->createChannel($shopBillingData);
        $order = $this->createOrder($channel);

        $this->processOrder($order, $taxationAddressResolver);
    }

    public function testEqualTaxationAddressCountryAndShopBillingDataCountry(): void
    {
        $taxationAddressResolver = $this->createTaxationAddressResolver('DE', true);

        $shopBillingData = $this->createShopBillingData('DE');
        $channel = $this->createChannel($shopBillingData);
        $order = $this->createOrder($channel);

        $this->processOrder($order, $taxationAddressResolver);
    }

    public function testValidForZeroTax(): void
    {
        $taxationAddressResolver = $this->createTaxationAddressResolver('FR', true);

        $shopBillingData = $this->createShopBillingData('DE');
        $channel = $this->createChannel($shopBillingData);

        $taxAdjustment = $this->createMock(AdjustmentInterface::class);
        $taxAdjustment->method('isNeutral')->willReturn(true);
        $taxAdjustment->method('getDetails')->willReturn(['shippingMethodCode' => 'Post']);
        $taxAdjustment->method('getAmount')->willReturn(5);

        $shippingAdjustment = $this->createMock(AdjustmentInterface::class);
        $shippingAdjustment->method('getDetails')->willReturn(['shippingMethodCode' => 'Post']);
        $shippingAdjustment->method('getAmount')->willReturn(25);
        $shippingAdjustment->expects(self::once())->method('setAmount')->with(20);

        $orderItem = $this->createMock(OrderItemInterface::class);
        $orderItem->method('getQuantity')->willReturn(1);
        $orderItem->method('getUnitPrice')->willReturn(25);
        $orderItem->expects(self::once())->method('setUnitPrice')->with(20);
        $orderItem->expects(self::once())->method('recalculateUnitsTotal');
        $orderItem->method('getAdjustmentsRecursively')->willReturn(new ArrayCollection([$taxAdjustment]));

        $order = $this->createOrder($channel, null);
        $order->expects(self::once())->method('removeAdjustmentsRecursively');

        $order->method('getItems')->willReturn(new ArrayCollection([$orderItem]));

        $order->method('getAdjustments')
            ->willReturnMap([
                [AdjustmentInterface::TAX_ADJUSTMENT, new ArrayCollection([$taxAdjustment])],
                [AdjustmentInterface::SHIPPING_ADJUSTMENT, new ArrayCollection([$shippingAdjustment])],
            ]);

        $this->processOrder($order, $taxationAddressResolver);
    }

    private function initVatNumberOrderProcessor(
        ?TaxationAddressResolverInterface $taxationAddressResolver = null,
        ?ZoneRepositoryInterface $zoneRepository = null,
        bool $isActive = true,
    ): VatNumberOrderProcessor {
        if ($taxationAddressResolver === null) {
            $taxationAddressResolver = $this->createMock(TaxationAddressResolverInterface::class);
        }

        if ($zoneRepository === null) {
            $zoneRepository = $this->createEuZoneRepository();
        }

        return new VatNumberOrderProcessor(
            $this->createMock(EntityManagerInterface::class),
            $zoneRepository,
            $taxationAddressResolver,
            $isActive
        );
    }

    private function processOrder(
        OrderInterface $order,
        ?TaxationAddressResolverInterface $taxationAddressResolver = null,
        ?ZoneRepositoryInterface $zoneRepository = null,
        bool $isActive = true,
    ): void {
        $vatNumberOrderProcessor = $this->initVatNumberOrderProcessor(
            $taxationAddressResolver,
            $zoneRepository,
            $isActive
        );

        $vatNumberOrderProcessor->process($order);
    }

    private function createOrder(
        ?Channel $channel = null,
        ?string $expectsNever = 'removeAdjustmentsRecursively'
    ): OrderInterface|MockObject {
        $order = $this->createMock(OrderInterface::class);
        $order->method('getChannel')->willReturn($channel);

        if (isset($expectsNever)) {
            $order->expects(self::never())->method($expectsNever);
        }

        return $order;
    }

    private function createChannel(?ShopBillingDataInterface $shopBillingData = null): Channel
    {
        $channel = $this->createMock(Channel::class);
        $channel->method('getShopBillingData')->willReturn($shopBillingData);

        return $channel;
    }

    private function createShopBillingData(string $countryCode): ShopBillingDataInterface
    {
        $shopBillingData = $this->createMock(ShopBillingDataInterface::class);
        $shopBillingData->method('getCountryCode')->willReturn($countryCode);

        return $shopBillingData;
    }

    private function createTaxationAddressResolver(
        string $countryCode,
        bool $validVatNumber
    ): TaxationAddressResolverInterface {
        $taxationAddress = $this->createMock(VatNumberAddressInterface::class);
        $taxationAddress->method('getCountryCode')->willReturn($countryCode);
        $taxationAddress->method('hasValidVatNumber')->willReturn($validVatNumber);

        $taxationAddressResolver = $this->createMock(TaxationAddressResolverInterface::class);
        $taxationAddressResolver->method('getTaxationAddressFromOrder')->willReturn($taxationAddress);

        return $taxationAddressResolver;
    }

    private function createZoneMember(string $code): ZoneMemberInterface
    {
        $zoneMember = $this->createMock(ZoneMemberInterface::class);
        $zoneMember->method('getCode')->willReturn($code);

        return $zoneMember;
    }

    private function createEuZoneRepository(): ZoneRepositoryInterface
    {
        $euZone = $this->createMock(ZoneInterface::class);
        $euZone->method('getMembers')
            ->willReturn(new ArrayCollection([
                $this->createZoneMember('DE'),
                $this->createZoneMember('FR'),
            ]));

        $zoneRepository = $this->createMock(ZoneRepositoryInterface::class);
        $zoneRepository->method('findOneBy')
            ->with(['code' => 'EU', 'scope' => Scope::ALL])
            ->willReturn($euZone);

        return $zoneRepository;
    }
}
