<?php
declare(strict_types=1);

namespace App\DTO;


class OrderSummaryData
{
    private int $productsTotalPrice;
    private int $productsTotalWeight;
    private string $routeHash;
    private int $routeLength;
    private string $departurePoint;
    private int $deliveryPrice;

    /**
     * @param int $productsTotalPrice
     * @param int $productsTotalWeight
     * @param string $routeHash
     * @param int $routeLength
     * @param string $departurePoint
     * @param int $deliveryPrice
     */
    public function __construct(
        int $productsTotalPrice,
        int $productsTotalWeight,
        string $routeHash,
        int $routeLength,
        string $departurePoint,
        int $deliveryPrice
    )
    {
        $this->productsTotalPrice = $productsTotalPrice;
        $this->productsTotalWeight = $productsTotalWeight;
        $this->routeHash = $routeHash;
        $this->routeLength = $routeLength;
        $this->departurePoint = $departurePoint;
        $this->deliveryPrice = $deliveryPrice;
    }

    public static function createFromRow(array $row): self
    {
        return new self(
            $row['products_total_price'],
            $row['products_total_weight'],
            $row['route_hash'],
            $row['route_length'],
            $row['departure_point'],
            $row['delivery_price']
        );
    }

    /**
     * @return int
     */
    public function getProductsTotalPrice(): int
    {
        return $this->productsTotalPrice;
    }

    /**
     * @param int $productsTotalPrice
     */
    public function setProductsTotalPrice(int $productsTotalPrice): void
    {
        $this->productsTotalPrice = $productsTotalPrice;
    }

    /**
     * @return int
     */
    public function getProductsTotalWeight(): int
    {
        return $this->productsTotalWeight;
    }

    /**
     * @param int $productsTotalWeight
     */
    public function setProductsTotalWeight(int $productsTotalWeight): void
    {
        $this->productsTotalWeight = $productsTotalWeight;
    }

    /**
     * @return string
     */
    public function getRouteHash(): string
    {
        return $this->routeHash;
    }

    /**
     * @param string $routeHash
     */
    public function setRouteHash(string $routeHash): void
    {
        $this->routeHash = $routeHash;
    }

    /**
     * @return int
     */
    public function getRouteLength(): int
    {
        return $this->routeLength;
    }

    /**
     * @param int $routeLength
     */
    public function setRouteLength(int $routeLength): void
    {
        $this->routeLength = $routeLength;
    }

    /**
     * @return string
     */
    public function getDeparturePoint(): string
    {
        return $this->departurePoint;
    }

    /**
     * @param string $departurePoint
     */
    public function setDeparturePoint(string $departurePoint): void
    {
        $this->departurePoint = $departurePoint;
    }

    /**
     * @return int
     */
    public function getDeliveryPrice(): int
    {
        return $this->deliveryPrice;
    }

    /**
     * @param int $deliveryPrice
     */
    public function setDeliveryPrice(int $deliveryPrice): void
    {
        $this->deliveryPrice = $deliveryPrice;
    }


}