<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\Order;
use App\Utils\PaginateHelper;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Order|null find($id, $lockMode = null, $lockVersion = null)
 * @method Order|null findOneBy(array $criteria, array $orderBy = null)
 * @method Order[]    findAll()
 * @method Order[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    public function getListPaginator(PaginateHelper $paginateHelper): Paginator
    {
        $dql = "SELECT o FROM  App\Entity\Order o ORDER BY o.id DESC";
        $query = $this->_em->createQuery($dql)
            ->setFirstResult($paginateHelper->getFirstResult())
            ->setMaxResults($paginateHelper->getSize());

        return new Paginator($query, false);

    }
}
