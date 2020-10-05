<?php
declare(strict_types=1);

namespace App\APIHelper;


class ProductsAPIHelper extends AbstractAPIHelper
{
    const PARAMETER_ID = 'id';
    const PARAMETER_PRICE = 'price';
    const PARAMETER_WEIGHT = 'weight';

    const DEFAULT_OUTPUT_PARAMETERS = [self::PARAMETER_ID, self::PARAMETER_PRICE, self::PARAMETER_WEIGHT];

    const SERVICE_NAME = 'Product service';

    /**
     * @param array $productIds
     * @param string[] $parameters
     * @return array
     */
    public function fetchProductsData(array $productIds, array $parameters = self::DEFAULT_OUTPUT_PARAMETERS): array
    {
        self::checkParameters($parameters, [self::PARAMETER_ID, self::PARAMETER_PRICE, self::PARAMETER_WEIGHT]);

        return self::sendRequest('GET', '/products', [
            'query' =>
                [
                    'ids' => implode(',', $productIds),
                    'params' => implode(',', $parameters)
                ]
        ]);

    }

    protected function getAPIServiceName(): string
    {
        return self::SERVICE_NAME;
    }
}