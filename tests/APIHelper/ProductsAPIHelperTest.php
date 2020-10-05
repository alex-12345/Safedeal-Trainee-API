<?php
declare(strict_types=1);

namespace App\Tests\APIHelper;


use App\APIHelper\ProductsAPIHelper;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProductsAPIHelperTest extends TestCase
{
    const FIXTURE_SERVER_URL = 'http://fixture-products-service.com';

    /**
     * @return array[]
     */
    public function fetchProductsDataProvider(): array
    {
        $emptyResponseHeaders = [];
        $jsonResponseHeaders = $emptyResponseHeaders + ["Content-type" => "application/json"];
        $notFoundJsonResponseHeaders = $jsonResponseHeaders + ['http_code' => 404];
        $badContentTypeResponse = $emptyResponseHeaders + ['http_code' => 200];
        $unavailableServiceResponse = $emptyResponseHeaders + ['http_code' => 500];

        return [
            [
                [
                    ['id' => 1, 'weight' => 10, 'price' => 600],
                    ['id' => 2, 'weight' => 1, 'price' => 200],
                    ['id' => 3, 'weight' => 3, 'price' => 1200],
                ],
                $jsonResponseHeaders, null, [1, 2, 3]
            ],
            [
                [
                    ['id' => 1, 'weight' => 10],
                    ['id' => 2, 'weight' => 1]
                ],
                $jsonResponseHeaders, null, [1, 2],
                true, [ProductsAPIHelper::PARAMETER_ID, ProductsAPIHelper::PARAMETER_WEIGHT]
            ],
            [
                [
                    ['id' => 1, 'weight' => 10],
                    ['id' => 2, 'weight' => 1]
                ],
                $jsonResponseHeaders, InvalidArgumentException::class, [1, 2],
                true, ['bad parameter']
            ],
            [
                [
                    'error' => '404',
                    'message' => 'Product with id 99990 not found'
                ],
                $notFoundJsonResponseHeaders, NotFoundHttpException::class, [1, 2, 99999]
            ],
            [
                [
                    'description' => 'no message parameter'
                ],
                $notFoundJsonResponseHeaders, HttpException::class, [1, 2, 99999]
            ],
            [
                null,
                $badContentTypeResponse, HttpException::class, [1, 2, 99999],
                false
            ],
            [
                [
                    'error' => '500',
                    'message' => 'Service unavailable!'
                ],
                $unavailableServiceResponse, HttpException::class, [1, 2, 99999]
            ]
        ];
    }

    /**
     * @dataProvider fetchProductsDataProvider
     * @param array|null $mockResponseContent
     * @param array $mockResponseHeaders
     * @param string|null $expectedException
     * @param array $productIDs
     * @param bool $responseContentIsJson
     * @param array|null $parameters
     */
    public function testFetchProductsData(
        ?array $mockResponseContent,
        array $mockResponseHeaders,
        ?string $expectedException,
        array $productIDs,
        bool $responseContentIsJson = true,
        ?array $parameters = null
    ): void
    {
        $finalResponseContent = ($responseContentIsJson) ? json_encode($mockResponseContent) : $mockResponseContent;
        $responses = [new MockResponse($finalResponseContent, $mockResponseHeaders)];
        $mockServerUrl = $_ENV['PRODUCTS_API_URL'] ?? self::FIXTURE_SERVER_URL;
        $client = new MockHttpClient($responses, $mockServerUrl);

        $logisticApiHelper = new ProductsAPIHelper($mockServerUrl, $client);
        if (!is_null($expectedException))
            $this->expectException($expectedException);
        if (is_null($parameters))
            $parameters = $logisticApiHelper::DEFAULT_OUTPUT_PARAMETERS;
        $responseContent = $logisticApiHelper->fetchProductsData($productIDs, $parameters);
        if (is_null($expectedException)) {
            $this->assertEquals($mockResponseContent, $responseContent);
        }
    }
}