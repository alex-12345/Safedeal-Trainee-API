<?php
declare(strict_types=1);

namespace App\Tests\Controller;


use App\DTO\OrderSummaryData;
use DateTime;
use DateTimeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Cache\Adapter\TraceableAdapter;
use Symfony\Contracts\Cache\ItemInterface;

class OrderControllerTest extends WebTestCase
{
    const SELLER_USER = 'seller';
    const COURIER_USER = 'courier';
    const CONSUMER_USER = 'consumer';
    const BAD_USER = 'bad_user';


    /**
     * @return array[]
     */
    public function createDeliveryPriceProvider(): array
    {
        $requests = $this->getPostRequests();

        $expectedResponse = ['delivery_price' => 7500];

        $expectedObjectCacheItem = new OrderSummaryData(6600, 12, '08ca6a0b-1848-4e80-a202-87afd9f23514', 300, 'departure 13', 7500);

        return [
            [true, self::CONSUMER_USER, $requests['correct_request'], 200, $expectedResponse, $expectedObjectCacheItem],
            [true, self::SELLER_USER, $requests['correct_request'], 200, $expectedResponse, $expectedObjectCacheItem],
            [true, self::CONSUMER_USER, $requests['correct_request'], 200, $expectedResponse],
            [true, self::BAD_USER, $requests['correct_request'], 401],
            [false, self::CONSUMER_USER, $requests['correct_request'], 400],
            [true, self::CONSUMER_USER, $requests['bad_data_request'], 404],
            [true, self::CONSUMER_USER, $requests['not_valid_request'], 400],
        ];

    }

    /**
     * @dataProvider createDeliveryPriceProvider
     * @param bool $jsonHeaderFlag
     * @param string|null $user
     * @param array|null $content
     * @param int $code
     * @param array|null $expectedJsonResponse
     * @param OrderSummaryData|null $expectedObjectCacheItem
     */
    public function testCreateDeliveryPrice(
        bool $jsonHeaderFlag,
        ?string $user,
        ?array $content,
        int $code,
        ?array $expectedJsonResponse = null,
        ?OrderSummaryData $expectedObjectCacheItem = null
    ): void
    {
        if (!is_null($expectedObjectCacheItem)) {
            $isHitFlag = true;
            $expectedCacheItem = serialize($expectedObjectCacheItem);
        } else {
            $isHitFlag = false;
            $expectedCacheItem = null;
        }

        $client = static::createClient();
        $client->getContainer()->set('cache.app', $this->getCacheMockService($isHitFlag, $expectedCacheItem));

        $serverOptions = [];
        if ($jsonHeaderFlag) $serverOptions['CONTENT_TYPE'] = 'application/json';
        if (!is_null($user)) $serverOptions['PHP_AUTH_USER'] = $serverOptions['PHP_AUTH_PW'] = $user;

        $client->request('POST', '/orders/delivery/price', [], [], $serverOptions, json_encode($content));

        $this->assertResponseStatusCodeSame($code);

        if (!is_null($expectedJsonResponse)) {
            $content = $client->getResponse()->getContent();
            $order = json_decode($content, true);
            $this->assertEquals(JSON_ERROR_NONE, json_last_error());

            $this->assertEquals($expectedJsonResponse, $order);
        }
    }


    /**
     * @return array[]
     */
    public function createProvider(): array
    {
        $requests = $this->getPostRequests();


        $expectedObjectCacheItem = new OrderSummaryData(6600, 12, '08ca6a0b-1848-4e80-a202-87afd9f23514', 300, 'departure 13', 7500);

        $expectedResponseParameterValues = [
            'products_total_price' => 6600,
            'delivery_route_hash' => '08ca6a0b-1848-4e80-a202-87afd9f23514',
            'delivery_route_length' => 300,
            'delivery_price' => 7500
        ];
        return [
            [true, self::CONSUMER_USER, $requests['correct_request'], 200, $expectedResponseParameterValues, $expectedObjectCacheItem],
            [true, self::SELLER_USER, $requests['correct_request'], 200, $expectedResponseParameterValues, $expectedObjectCacheItem],
            [true, self::CONSUMER_USER, $requests['correct_request'], 200, $expectedResponseParameterValues],
            [true, self::BAD_USER, $requests['correct_request'], 401],
            [false, self::CONSUMER_USER, $requests['correct_request'], 400],
            [true, self::CONSUMER_USER, $requests['bad_data_request'], 404],
            [true, self::CONSUMER_USER, $requests['not_valid_request'], 400],
        ];
    }

    /**
     * @dataProvider createProvider
     * @param bool $jsonHeaderFlag
     * @param string|null $user
     * @param array|null $content
     * @param int $code
     * @param array|null $expectedResponseParameterValues
     * @param OrderSummaryData|null $expectedObjectCacheItem
     */
    public function testCreate(
        bool $jsonHeaderFlag,
        ?string $user,
        ?array $content,
        int $code,
        array $expectedResponseParameterValues = null,
        ?OrderSummaryData $expectedObjectCacheItem = null
    ): void
    {
        if (!is_null($expectedObjectCacheItem)) {
            $isHitFlag = true;
            $expectedCacheItem = serialize($expectedObjectCacheItem);
        } else {
            $isHitFlag = false;
            $expectedCacheItem = null;
        }

        $client = static::createClient();
        $client->getContainer()->set('cache.app', $this->getCacheMockService($isHitFlag, $expectedCacheItem));

        $serverOptions = [];
        if ($jsonHeaderFlag) $serverOptions['CONTENT_TYPE'] = 'application/json';
        if (!is_null($user)) $serverOptions['PHP_AUTH_USER'] = $serverOptions['PHP_AUTH_PW'] = $user;

        $client->request('POST', '/orders', [], [], $serverOptions, json_encode($content));

        $this->assertResponseStatusCodeSame($code);

        if (!is_null($expectedResponseParameterValues)) {
            $this->assertResponseHeaderSame('Content-Type', 'application/json');
            $content = $client->getResponse()->getContent();
            $order = json_decode($content, true);
            $this->assertEquals(JSON_ERROR_NONE, json_last_error());

            $this->assertEquals($user, $order['consumer']);

            $this->assertNotFalse(DateTime::createFromFormat(DateTimeInterface::RFC3339, $order['created_at']));

            $this->assertEquals($expectedResponseParameterValues['products_total_price'], $order['products_total_price']);
            $this->assertEquals($expectedResponseParameterValues['delivery_route_hash'], $order['delivery_route_hash']);
            $this->assertEquals($expectedResponseParameterValues['delivery_route_length'], $order['delivery_route_length']);
            $this->assertEquals($expectedResponseParameterValues['delivery_price'], $order['delivery_price']);

            $this->assertIsArray($order['products']);
            $this->assertIsInt($order['products'][0]['id']);
        }
    }

    /**
     * @return array[]
     */
    public function readProvider(): array
    {
        return [
            [1, 401, null],
            [1, 401, self::BAD_USER],
            [1, 200, self::SELLER_USER],
            [1, 403, self::CONSUMER_USER],
            [3, 200, self::CONSUMER_USER, false, true],
            [-3, 404, self::CONSUMER_USER],
            [999, 404, self::CONSUMER_USER],
            [1, 200, self::SELLER_USER, true],
            [4, 200, self::COURIER_USER, true]
        ];
    }

    /**
     * @dataProvider readProvider
     *
     * @param int $id
     * @param int $code
     * @param string|null $user
     * @param bool $checkContentFlag
     * @param bool $isOrderConsumer
     */
    public function testRead(
        int $id,
        int $code,
        ?string $user,
        bool $checkContentFlag = false,
        bool $isOrderConsumer = false
    ): void
    {
        $client = static::createClient();

        $serverOptions = [];
        if (!is_null($user)) $serverOptions['PHP_AUTH_USER'] = $serverOptions['PHP_AUTH_PW'] = $user;

        $client->request('GET', '/orders/' . $id, [], [], $serverOptions);

        $this->assertResponseStatusCodeSame($code);

        if ($checkContentFlag) {
            $this->assertResponseHeaderSame('Content-Type', 'application/json');
            $content = $client->getResponse()->getContent();
            $order = json_decode($content, true);
            $this->assertEquals(JSON_ERROR_NONE, json_last_error());

            if ($isOrderConsumer) {
                $this->assertEquals($user, $order['consumer']);
            }
            $this->assertNotFalse(DateTime::createFromFormat(DateTimeInterface::RFC3339, $order['created_at']));
            $this->assertIsString($order['delivery_route_hash']);
            $this->assertIsInt($order['delivery_route_length']);
            $this->assertIsArray($order['products']);
            $this->assertIsInt($order['products'][0]['id']);
        }

    }

    /**
     * @return array[]
     */
    public function listProvider(): array
    {
        return [
            [[], 200, self::SELLER_USER, true, 10],
            [['page' => ['number' => 4, 'size' => 6]], 200, self::SELLER_USER, true, 2],
            [['page' => ['number' => 1, 'size' => 5]], 403, self::COURIER_USER],
            [['page' => ['number' => 1, 'size' => 5]], 403, self::CONSUMER_USER],
            [['page' => ['number' => 1, 'size' => 5]], 401, self::BAD_USER],
            [['page' => 'non_array'], 200, self::SELLER_USER, true, 10],
            [['page' => ['number' => 5]], 404, self::SELLER_USER],
            [['page' => ['number' => 6, 'size' => 5]], 404, self::SELLER_USER]
        ];

    }

    /**
     * @dataProvider listProvider
     * @param array $page
     * @param int $code
     * @param string|null $user
     * @param bool $checkContentFlag
     * @param int|null $ordersCount
     */
    public function testList(
        array $page,
        int $code,
        ?string $user,
        bool $checkContentFlag = false,
        ?int $ordersCount = null
    ): void
    {
        $client = static::createClient();

        $serverOptions = [];
        if (!is_null($user)) $serverOptions['PHP_AUTH_USER'] = $serverOptions['PHP_AUTH_PW'] = $user;

        $parameters = array_merge([], $page);

        $client->request('GET', '/orders', $parameters, [], $serverOptions);

        $this->assertResponseStatusCodeSame($code);

        if ($checkContentFlag) {
            $this->assertResponseHeaderSame('Content-Type', 'application/json');

            $orders = json_decode($client->getResponse()->getContent(), true);
            $this->assertEquals(JSON_ERROR_NONE, json_last_error());
            $this->assertCount($ordersCount, $orders);

            $firstOrder = $orders[0];

            $this->assertEquals([], array_diff_key(
                ['id', 'created_at', 'consumer', 'delivery_departure_point', 'delivery_destination_point', 'total_price'],
                array_keys($firstOrder)
            ));

            $this->assertNotFalse(DateTime::createFromFormat(DateTimeInterface::RFC3339, $firstOrder['created_at']));

        }
    }

    private function getPostRequests(): array
    {
        return [
            'correct_request' => [
                'delivery_destination' => 'destination place',
                'products' => [
                    ['id' => 1, 'amount' => 5],
                    ['id' => 4, 'amount' => 2]
                ]
            ],
            'bad_data_request' => [
                'delivery_destination' => 'bad destination place',
                'products' => [
                    ['id' => 99999, 'amount' => 5]
                ]
            ],
            'not_valid_request' => [
                'delivery_destination' => 'destination place',
                'products' => []
            ]
        ];
    }

    private function getCacheMockService(bool $isHitFlag, ?string $expectedCacheItem = null): MockObject
    {
        $itemMock = $this->createMock(ItemInterface::class);

        $itemMock->expects($this->any())->method('isHit')->willReturn($isHitFlag);
        if ($isHitFlag) {
            $itemMock->expects($this->any())->method('get')->willReturn($expectedCacheItem);
        }
        $itemMock->expects($this->any())->method('set')->willReturn($itemMock);
        $itemMock->expects($this->any())->method('expiresAfter')->willReturn($itemMock);

        $cache = $this->createMock(TraceableAdapter::class);
        $cache->expects($this->any())->method('getItem')->willReturn($itemMock);
        $cache->expects($this->any())->method('save')->willReturn(true);

        return $cache;
    }

}