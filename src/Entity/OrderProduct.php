<?php
declare(strict_types=1);

namespace App\Entity;

use App\Repository\OrderProductRepository;
use Doctrine\ORM\Mapping as ORM;
use OpenApi\Annotations as OA;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;

/**
 * @OA\Schema(
 *     schema="OrderProductsAmountItem",
 *     type="object",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="amount", type="integer")
 * )
 * @ORM\Entity(repositoryClass=OrderProductRepository::class)
 */
class OrderProduct
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"full"})
     * @SerializedName("id")
     */
    private int $productId;

    /**
     * @ORM\ManyToOne(targetEntity="Order", inversedBy="orderProducts")
     * @ORM\JoinColumn(name="order_id", referencedColumnName="id")
     */
    private Order $order;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"full"})
     */
    private int $amount;

    /**
     * OrderProduct constructor.
     * @param int $productId
     * @param int $amount
     */
    public function __construct(int $productId, int $amount)
    {
        $this->productId = $productId;
        $this->amount = $amount;
    }

    public static function createFromRow(array $row): self
    {
        return new self($row['id'], $row['amount']);

    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProductId(): int
    {
        return $this->productId;
    }

    public function setProductId(int $productId): self
    {
        $this->productId = $productId;

        return $this;
    }

    public function getOrder(): Order
    {
        return $this->order;
    }

    public function setOrder(Order $order): self
    {
        $this->order = $order;

        return $this;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

}
