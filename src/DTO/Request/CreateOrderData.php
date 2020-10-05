<?php
declare(strict_types=1);

namespace App\DTO\Request;

use OpenApi\Annotations as OA;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @OA\RequestBody(
 *     request="Order",
 *     required=true,
 *     @OA\JsonContent(ref="#/components/schemas/OrderInput")
 * )
 * @OA\Schema(
 *     schema="OrderInput",
 *     @OA\Property(property="delivery_destination", type="string", example="destination place"),
 *     @OA\Property(property="products", type="array", @OA\Items(ref="#/components/schemas/OrderProductsAmountItem"),
 *     example={{"id"=1,"amount"=5},{"id"=4,"amount"=2}})
 * )
 */
class CreateOrderData implements RequestDataInterface
{
    /**
     * @Assert\NotNull()
     * @Assert\Length(min = 2, max = 50, allowEmptyString = false)
     */
    private string $deliveryDestination;

    /**
     * @Assert\NotNull()
     * @Assert\Count(min="1")
     * @Assert\All(
     *     constraints={
     *     @Assert\Collection(
     *          fields={
     *              "id"={
     *                  @Assert\NotNull(),
     *                  @Assert\GreaterThan(0)
     *              },
     *              "amount"={
     *                  @Assert\NotNull(),
     *                  @Assert\GreaterThan(0)
     *              }
     *          },
     *
     *     )
     *     }
     * )
     */
    private array $products;

    public function getDeliveryDestination(): string
    {
        return $this->deliveryDestination;
    }

    public function setDeliveryDestination(string $deliveryDestination): void
    {
        $this->deliveryDestination = $deliveryDestination;
    }

    public function getProducts(): array
    {
        return $this->products;
    }

    public function setProducts(array $products): void
    {
        usort($products, fn($l, $r) => $l['id'] > $r['id']);
        $this->products = $products;
    }

    public function getHash(): string
    {
        return sha1(serialize(self::class));
    }


}