<?php
declare(strict_types=1);

namespace App\Tests\Utils;


use App\Utils\OrderCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class OrderCalculatorTest extends TestCase
{
    public function CalculateOrderProductsSummaryProvider(): array
    {
        return [
            [false, [
                ['id' => 1, 'weight' => 10, 'price' => 1000, 'amount' => 10],
                ['id' => 2, 'weight' => 21, 'price' => 1200, 'amount' => 11]
            ], [OrderCalculator::PRODUCTS_TOTAL_PRICE => 23200, OrderCalculator::PRODUCTS_TOTAL_WEIGHT => 331]],
            [true, [[]]],
            [true, [['id' => 1, 'weight' => -10, 'price' => 1000, 'amount' => 10]]],
            [true, [['id' => 1, 'weight' => 10, 'price' => 1000, 'amount2' => 10]]],
        ];
    }

    /**
     * @dataProvider CalculateOrderProductsSummaryProvider
     * @param bool $expectException
     * @param array $products
     * @param array|null $expectResult
     */
    public function testCalculateOrderProductsSummary(bool $expectException, array $products, ?array $expectResult = null): void
    {
        $orderCalculator = new OrderCalculator();

        if ($expectException) {
            $this->expectException(InvalidArgumentException::class);
        }
        $result = $orderCalculator->calculateOrderProductsSummary($products);
        if (!is_null($expectResult)) {
            $this->assertEquals($expectResult, $result);
        }
    }


    public function CalculateDeliveryPriceRowFormatProvider(): array
    {
        return [
            [true, 100, -1, 12],
            [true, 100, 11, -1],
            [false, OrderCalculator::FLOOR_FREE_DELIVERY + 1, 100, 100, 0],
            [false, 100, 100, 100, 3500],
            [false, 100, 120, 100, 5500],
            [false, 100, 2, 100, 500]
        ];
    }

    /**
     * @dataProvider CalculateDeliveryPriceRowFormatProvider
     * @param bool $expectException
     * @param int $productsTotalPrice
     * @param int $productsTotalWeight
     * @param int $deliveryRouteLength
     * @param int|null $expectResult
     */
    public function testCalculateDeliveryPriceRowFormat(
        bool $expectException,
        int $productsTotalPrice,
        int $productsTotalWeight,
        int $deliveryRouteLength,
        ?int $expectResult = null
    ): void
    {
        $orderCalculator = new OrderCalculator();

        if ($expectException) {
            $this->expectException(InvalidArgumentException::class);
        }

        $result = $orderCalculator->CalculateDeliveryPriceRowFormat($productsTotalPrice, $productsTotalWeight, $deliveryRouteLength);

        if (!is_null($expectResult)) {
            $this->assertEquals([$orderCalculator::DELIVERY_PRICE => $expectResult], $result);
        }

    }

}