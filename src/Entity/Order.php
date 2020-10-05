<?php
declare(strict_types=1);

namespace App\Entity;

use App\Repository\OrderRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use OpenApi\Annotations as OA;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;

/**
 *
 * @OA\Schema(
 *     schema="OrderListItem",
 *     type="object",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="consumer", type="string"),
 *     @OA\Property(property="delivery_departure_point", type="string"),
 *     @OA\Property(property="delivery_destination_point", type="string"),
 *     @OA\Property(property="total_price", type="integer")
 * )
 * @OA\Schema(
 *     schema="OrderFullItem",
 *     type="object",
 *     allOf={@OA\Schema(ref="#/components/schemas/OrderListItem")},
 *     @OA\Property(property="products_total_price", type="integer"),
 *     @OA\Property(property="products_total_weight", type="integer"),
 *     @OA\Property(property="delivery_route_hash", type="string"),
 *     @OA\Property(property="delivery_routhe_length", type="string"),
 *     @OA\Property(property="delivery_price", type="integer"),
 *     @OA\Property(property="products", type="array", @OA\Items(ref="#/components/schemas/OrderProductsAmountItem"))
 * )
 * @ORM\Entity(repositoryClass=OrderRepository::class)
 * @ORM\Table(name="`order`")
 */
class Order
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"list","full"})
     */
    private ?int $id;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"list","full"})
     */
    private DateTimeInterface $createdAt;

    /**
     * @ORM\Column(type="string")
     * @Groups({"list","full"})
     */
    private string $consumer;
    /**
     * @ORM\Column(type="integer")
     * @Groups({"full"})
     */
    private int $productsTotalPrice;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"full"})
     */
    private int $productsTotalWeight;

    /**
     * @ORM\Column(type="string")
     * @Groups({"full"})
     */
    private string $deliveryRouteHash;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"full"})
     */
    private int $deliveryRouteLength;

    /**
     * @ORM\Column(type="string")
     * @Groups({"list","full"})
     */
    private string $deliveryDeparturePoint;

    /**
     * @ORM\Column(type="string")
     * @Groups({"list","full"})
     */
    private string $deliveryDestinationPoint;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"full"})
     */
    private int $deliveryPrice;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"list","full"})
     */
    private int $totalPrice;

    /**
     * @ORM\OneToMany(targetEntity="OrderProduct", mappedBy="order")
     * @Groups({"full"})
     * @SerializedName("products")
     */
    private Collection $orderProducts;

    public function __construct()
    {
        $this->createdAt = new DateTime();
        $this->orderProducts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getProductsTotalPrice(): int
    {
        return $this->productsTotalPrice;
    }

    public function setProductsTotalPrice(int $productsTotalPrice): self
    {
        $this->productsTotalPrice = $productsTotalPrice;

        return $this;
    }

    public function getProductsTotalWeight(): int
    {
        return $this->productsTotalWeight;
    }

    public function setProductsTotalWeight(int $productsTotalWeight): self
    {
        $this->productsTotalWeight = $productsTotalWeight;

        return $this;
    }

    public function getDeliveryRouteHash(): string
    {
        return $this->deliveryRouteHash;
    }

    public function setDeliveryRouteHash(string $deliveryRouteHash): self
    {
        $this->deliveryRouteHash = $deliveryRouteHash;

        return $this;
    }

    public function getDeliveryRouteLength(): int
    {
        return $this->deliveryRouteLength;
    }

    public function setDeliveryRouteLength(int $deliveryRouteLength): self
    {
        $this->deliveryRouteLength = $deliveryRouteLength;

        return $this;
    }

    public function getDeliveryDeparturePoint(): string
    {
        return $this->deliveryDeparturePoint;
    }

    public function setDeliveryDeparturePoint(string $deliveryDeparturePoint): self
    {
        $this->deliveryDeparturePoint = $deliveryDeparturePoint;

        return $this;
    }

    public function getDeliveryDestinationPoint(): string
    {
        return $this->deliveryDestinationPoint;
    }

    public function setDeliveryDestinationPoint(string $deliveryDestinationPoint): self
    {
        $this->deliveryDestinationPoint = $deliveryDestinationPoint;

        return $this;
    }

    public function getDeliveryPrice(): int
    {
        return $this->deliveryPrice;
    }

    public function setDeliveryPrice(int $deliveryPrice): self
    {
        $this->deliveryPrice = $deliveryPrice;

        return $this;
    }

    public function getTotalPrice(): int
    {
        return $this->totalPrice;
    }

    public function setTotalPrice(?int $totalPrice = null): self
    {
        $this->totalPrice = (isset($totalPrice)) ? $totalPrice : $this->deliveryPrice + $this->productsTotalPrice;

        return $this;
    }

    public function getConsumer(): string
    {
        return $this->consumer;
    }

    public function setConsumer(string $consumer): self
    {
        $this->consumer = $consumer;

        return $this;
    }

    public function addOrderProduct(OrderProduct $orderProduct): void
    {
        if (!$this->orderProducts->contains($orderProduct)) {
            $this->orderProducts[] = $orderProduct;
            $orderProduct->setOrder($this);
        }
    }

    public function getOrderProducts(): Collection
    {
        return $this->orderProducts;
    }


}
