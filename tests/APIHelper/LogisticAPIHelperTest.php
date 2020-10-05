<?php
declare(strict_types=1);

namespace App\Tests\APIHelper;


use App\APIHelper\LogisticAPIHelper;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class LogisticAPIHelperTest extends TestCase
{

    const FIXTURE_SERVER_URL = 'http://fixture-logistic-service.com';

    /**
     * @return array[]
     */
    public function createDeliveryRouteProvider(): array
    {
        $emptyResponseHeaders = [];
        $jsonResponseHeaders = $emptyResponseHeaders + ["Content-type" => "application/json"];
        $notFoundJsonResponseHeaders = $jsonResponseHeaders + ['http_code' => 404];
        $badContentTypeResponse = $emptyResponseHeaders + ['http_code' => 200];
        $unavailableServiceResponse = $emptyResponseHeaders + ['http_code' => 500];

        return [
            [
                [
                    'route_hash' => '08ca6a0b-1848-4e80-a202-87afd9f23514',
                    'route_length' => 12,
                    'departure_point' => 'departure point name'
                ],
                $jsonResponseHeaders, null,
                [['id' => 21, 'amount' => 12], ['id' => 7, 'amount' => 2]], 'destination point name',
            ],
            [
                [
                    'route_hash' => '5b3994c6-0681-11eb-adc1-0242ac120002',
                    'route_length' => 12
                ],
                $jsonResponseHeaders, null,
                [['id' => 22, 'amount' => 12]], 'destination point name 122', true,
                [LogisticAPIHelper::ROUTE_HASH, LogisticAPIHelper::ROUTE_LENGTH]
            ],
            [
                [
                    'route_hash' => '5b3994c6-0681-11eb-adc1-0242ac120002',
                    'route_length' => 12
                ],
                $jsonResponseHeaders, InvalidArgumentException::class,
                [['id' => 22, 'amount' => 12]], 'destination point name 122', true,
                ['bad parameter']
            ],
            [
                [
                    'error' => '404',
                    'message' => 'Product with id 9999 not found'
                ],
                $notFoundJsonResponseHeaders, NotFoundHttpException::class,
                [['id' => 99999, 'amount' => 5]], 'destination point name',
            ],
            [
                [
                    'description' => 'no message parameter'
                ],
                $notFoundJsonResponseHeaders, HttpException::class,
                [['id' => 99999, 'amount' => 5]], 'destination point name',
            ],
            [
                null,
                $badContentTypeResponse, HttpException::class,
                [['id' => 11, 'amount' => 5]], 'bad destination point name', false
            ],
            [
                [
                    'error' => '500',
                    'message' => 'Service unavailable!'
                ],
                $unavailableServiceResponse, HttpException::class,
                [['id' => -99999, 'amount' => 5]], 'destination point name',
            ]
        ];
    }

    /**
     * @dataProvider createDeliveryRouteProvider
     * @param array|null $mockResponseContent
     * @param array $mockResponseHeaders
     * @param string|null $expectedException
     * @param array $products
     * @param string $deliveryDestination
     * @param bool $responseContentIsJson
     * @param array|null $parameters
     */
    public function testCreateDeliveryRoute(
        ?array $mockResponseContent,
        array $mockResponseHeaders,
        ?string $expectedException,
        array $products,
        string $deliveryDestination,
        bool $responseContentIsJson = true,
        ?array $parameters = null
    ): void
    {
        $finalResponseContent = ($responseContentIsJson) ? json_encode($mockResponseContent) : $mockResponseContent;
        $responses = [new MockResponse($finalResponseContent, $mockResponseHeaders)];
        $mockServerUrl = $_ENV['LOGISTIC_API_URL'] ?? self::FIXTURE_SERVER_URL;
        $client = new MockHttpClient($responses, $mockServerUrl);

        $logisticApiHelper = new LogisticAPIHelper($mockServerUrl, $client);
        if (!is_null($expectedException))
            $this->expectException($expectedException);
        if (is_null($parameters))
            $parameters = $logisticApiHelper::DEFAULT_OUTPUT_PARAMETERS;
        $responseContent = $logisticApiHelper->createDeliveryRoute($products, $deliveryDestination, $parameters);
        if (is_null($expectedException)) {
            $this->assertEquals($mockResponseContent, $responseContent);
        }
    }

}