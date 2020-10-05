<?php
declare(strict_types=1);

namespace App\APIHelper;


class LogisticAPIHelper extends AbstractAPIHelper
{

    const ROUTE_HASH = 'route_hash';
    const ROUTE_DEPARTURE_POINT = 'departure_point';
    const ROUTE_LENGTH = 'route_length';

    const DEFAULT_OUTPUT_PARAMETERS = [self::ROUTE_HASH, self::ROUTE_DEPARTURE_POINT, self::ROUTE_LENGTH];

    const SERVICE_NAME = 'Logistic service';

    /**
     * @param array[] $products
     * @param string $deliveryDestination
     * @param string[] $parameters
     * @return array
     */
    public function createDeliveryRoute(
        array $products,
        string $deliveryDestination,
        array $parameters = self::DEFAULT_OUTPUT_PARAMETERS
    ): array
    {
        self::checkParameters($parameters, [self::ROUTE_HASH, self::ROUTE_DEPARTURE_POINT, self::ROUTE_LENGTH]);

        return self::sendRequest('POST', '/routes/delivery', [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'query' =>
                [
                    'params' => implode(',', $parameters)
                ],
            'json' => [
                'destination' => $deliveryDestination,
                'products' => $products
            ]
        ]);
    }

    protected function getAPIServiceName(): string
    {
        return self::SERVICE_NAME;
    }
}