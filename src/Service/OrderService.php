<?php
declare(strict_types=1);

namespace App\Service;


use App\APIHelper\LogisticAPIHelper;
use App\APIHelper\ProductsAPIHelper;
use App\DataMapper\OrderMapper;
use App\DTO\Request\CreateOrderData;
use App\DTO\OrderSummaryData;
use App\Entity\Order;
use App\Entity\OrderProduct;
use App\Utils\OrderCalculator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\Cache\CacheInterface;

class OrderService
{
    const CACHE_ORDER_TTL = 300;
    const CACHE_ORDER_SUMMARY_PREFIX = 'order_summary';

    private CacheInterface $cache;
    private ProductsAPIHelper $productsAPIHelper;
    private LogisticAPIHelper $logisticAPIHelper;
    private OrderCalculator $orderCalculator;
    private NormalizerInterface $normalizer;

    public function __construct(
        CacheInterface $cache,
        ProductsAPIHelper $productsAPIHelper,
        LogisticAPIHelper $logisticAPIHelper,
        OrderCalculator $orderCalculator,
        NormalizerInterface $normalizer
    )
    {
        $this->cache = $cache;
        $this->productsAPIHelper = $productsAPIHelper;
        $this->logisticAPIHelper = $logisticAPIHelper;
        $this->orderCalculator = $orderCalculator;
        $this->normalizer = $normalizer;
    }

    public function createOrderSummary(CreateOrderData $dto): OrderSummaryData
    {
        $orderSummaryCacheItem = $this->cache->getItem(self::CACHE_ORDER_SUMMARY_PREFIX . '_' . $dto->getHash());

        if ($orderSummaryCacheItem->isHit()) {
            $orderSummary = unserialize($orderSummaryCacheItem->get());
        } else {
            $orderSummary = self::computeOrderSummary($dto->getProducts(), $dto->getDeliveryDestination());

            $this->cache->save($orderSummaryCacheItem->set(serialize($orderSummary))->expiresAfter(self::CACHE_ORDER_TTL));
        }

        return $orderSummary;
    }

    public function createOrder(
        CreateOrderData $createOrderData,
        OrderSummaryData $orderSummaryData,
        EntityManagerInterface $em,
        UserInterface $user
    ): Order
    {
        $order = OrderMapper::usernameAndDTOsToOrder($user->getUsername(), $orderSummaryData, $createOrderData);

        $em->persist($order);

        foreach ($createOrderData->getProducts() as $product) {
            $orderProduct = OrderProduct::createFromRow($product);
            $order->addOrderProduct($orderProduct);
            $em->persist($orderProduct);
        }

        $em->flush();

        return $order;

    }

    private function computeOrderSummary(array $orderProducts, string $deliveryDestination): OrderSummaryData
    {
        $productIds = array_map(fn($product) => $product['id'], $orderProducts);
        $productsMetadata = $this->productsAPIHelper->fetchProductsData($productIds);

        $orderLogisticSummary = $this->logisticAPIHelper->createDeliveryRoute($orderProducts, $deliveryDestination);

        $orderProductsWithMetadata = array_map(
            fn($orderProduct, $productMetadata) => array_merge($orderProduct, $productMetadata),
            $productsMetadata, $orderProducts
        );

        $orderProductsSummary = $this->orderCalculator->calculateOrderProductsSummary($orderProductsWithMetadata);

        $deliveryPriceRowFormat = $this->orderCalculator->calculateDeliveryPriceRowFormat(
            $orderProductsSummary[OrderCalculator::PRODUCTS_TOTAL_PRICE],
            $orderProductsSummary[OrderCalculator::PRODUCTS_TOTAL_WEIGHT],
            $orderLogisticSummary[LogisticAPIHelper::ROUTE_LENGTH]
        );

        return OrderSummaryData::createFromRow(array_merge($orderProductsSummary, $orderLogisticSummary, $deliveryPriceRowFormat));

    }

}