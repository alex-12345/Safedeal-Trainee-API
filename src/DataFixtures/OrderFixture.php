<?php
declare(strict_types=1);

namespace App\DataFixtures;


use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;

class OrderFixture extends AbstractFixture
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;

    }

    public function load(ObjectManager $manager)
    {
        $manager->getConnection()->exec("ALTER SEQUENCE order_id_seq RESTART WITH 1");
        $user = ['seller', 'courier', 'consumer'];
        for ($i = 0; $i < 20; $i++) {
            $order = new Order();
            $order->setConsumer($user[$i % 3]);

            $order->setDeliveryRouteLength($this->getRandomUnsignedInt(1200));
            $order->setDeliveryPrice($this->getRandomUnsignedInt(60000));

            $order->setDeliveryRouteHash(uniqid());
            $order->setDeliveryDestinationPoint($this->getRandomWord());
            $order->setDeliveryDeparturePoint($this->getRandomWord());

            $order->setProductsTotalWeight($this->getRandomUnsignedInt(120));
            $order->setProductsTotalPrice($this->getRandomUnsignedInt(5000));

            $order->setTotalPrice();

            $manager->persist($order);
        }
        $manager->flush();
    }
}