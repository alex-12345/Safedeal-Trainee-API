<?php
declare(strict_types=1);

namespace App\DataMapper;


use App\DTO\Request\CreateOrderData;
use App\DTO\OrderSummaryData;
use App\Entity\Order;

class OrderMapper
{

    public static function usernameAndDTOsToOrder(
        string $username,
        OrderSummaryData $orderSummaryData,
        CreateOrderData $createOrderData
    ): Order
    {
        $order = new Order();
        $order->setConsumer($username);
        $order->setDeliveryDeparturePoint($orderSummaryData->getDeparturePoint());
        $order->setDeliveryDestinationPoint($createOrderData->getDeliveryDestination());
        $order->setDeliveryPrice($orderSummaryData->getDeliveryPrice());
        $order->setDeliveryRouteHash($orderSummaryData->getRouteHash());
        $order->setDeliveryRouteLength($orderSummaryData->getRouteLength());
        $order->setProductsTotalPrice($orderSummaryData->getProductsTotalPrice());
        $order->setProductsTotalWeight($orderSummaryData->getProductsTotalWeight());
        $order->setTotalPrice();

        return $order;
    }

}