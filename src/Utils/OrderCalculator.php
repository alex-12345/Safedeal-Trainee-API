<?php
declare(strict_types=1);

namespace App\Utils;


use InvalidArgumentException;

class OrderCalculator
{
    const FLOOR_FREE_DELIVERY = 50000;

    const PRODUCTS_TOTAL_PRICE = 'products_total_price';
    const PRODUCTS_TOTAL_WEIGHT = 'products_total_weight';
    const DELIVERY_PRICE = 'delivery_price';

    /**
     * @param array $products
     * @return array
     */
    public function calculateOrderProductsSummary(array $products): array
    {
        if (empty($products)) throw new InvalidArgumentException('Products array is empty!');
        $productsTotalPrice = 0;
        $productsTotalWeight = 0;

        foreach ($products as $product) {
            if (
                !isset($product['price'])
                || !isset($product['weight'])
                || !isset($product['amount'])
                || $product['price'] < 0
                || $product['weight'] < 0
                || $product['amount'] < 1
            ) throw new InvalidArgumentException('Bad content!');

            $productsTotalPrice += $product['price'] * $product['amount'];
            $productsTotalWeight += $product['weight'] * $product['amount'];
        }

        return [
            self::PRODUCTS_TOTAL_PRICE => $productsTotalPrice,
            self::PRODUCTS_TOTAL_WEIGHT => $productsTotalWeight
        ];


    }

    /**
     * Delivery pricing policy stub
     * @param int $productsTotalPrice
     * @param int $productsTotalWeight
     * @param int $deliveryRouteLength
     * @return int
     */
    public function calculateDeliveryPrice(int $productsTotalPrice, int $productsTotalWeight, int $deliveryRouteLength): int
    {
        if ($productsTotalPrice < 0 || $productsTotalWeight < 0 || $deliveryRouteLength < 0)
            throw new InvalidArgumentException();

        if ($productsTotalPrice >= self::FLOOR_FREE_DELIVERY)
            return 0;

        $deliveryPrice = 500;

        if ($productsTotalWeight > 100) {
            $deliveryPrice += 3000 + ($deliveryRouteLength * 20);
        } elseif ($productsTotalWeight > 5) {
            $deliveryPrice += 1000 + ($deliveryRouteLength * 20);
        }

        return $deliveryPrice;

    }

    public function calculateDeliveryPriceRowFormat(
        int $productsTotalPrice,
        int $productsTotalWeight,
        int $deliveryRouteLength
    ): array
    {
        return [self::DELIVERY_PRICE => self::calculateDeliveryPrice(
            $productsTotalPrice, $productsTotalWeight, $deliveryRouteLength
        )];
    }
}