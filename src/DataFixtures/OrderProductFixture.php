<?php
declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Order;
use App\Entity\OrderProduct;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class OrderProductFixture extends AbstractFixture implements DependentFixtureInterface
{

    public function load(ObjectManager $manager)
    {
        $manager->getConnection()->exec("ALTER SEQUENCE order_product_id_seq RESTART WITH 1");
        for ($i = 0; $i < 60; $i++) {
            $orderProduct = new OrderProduct($this->getRandomUnsignedInt(10000), $this->getRandomUnsignedInt(50));
            $order = $manager->getReference(Order::class, $i % 20 + 1);
            $orderProduct->setOrder($order);
            $manager->persist($orderProduct);
        }
        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            OrderFixture::class,
        ];
    }
}