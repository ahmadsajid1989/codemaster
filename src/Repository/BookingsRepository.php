<?php

namespace App\Repository;

use App\Entity\Bookings;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Bookings|null find($id, $lockMode = null, $lockVersion = null)
 * @method Bookings|null findOneBy(array $criteria, array $orderBy = null)
 * @method Bookings[]    findAll()
 * @method Bookings[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BookingsRepository extends ServiceEntityRepository
{
    /**
     * BookingsRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Bookings::class);
    }

    // /**
    //  * @return Bookings[] Returns an array of Bookings objects
    //  */

    /**
     * @param $room
     * @param $arival
     *
     * @return int|mixed|string
     * @throws \Doctrine\DBAL\DBALException
     */
    public function findByFreeRoomBycheckinAndCheckout($room,$arival, $checkout)
    {
        $conn = $this->getEntityManager()->getConnection();


        $sql = '
            SELECT * FROM bookings b
            join rooms r             
            where  :arrival between b.arrival and b.checkout
            or :checkout between b.arrival and b.checkout
            and r.id = :room
                      
            ';
        $stmt = $conn->prepare($sql);
        $stmt->execute(['arrival' => $arival,'checkout'=> $checkout, 'room' => $room]);

        $result = count($stmt->fetchAll());

        if ($result > 0){
            $out = 1;
        }else{
            $out = 0;
        }

        return $out;
    }

    /**
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function findBookingDetails(): array
    {

        $conn = $this->getEntityManager()->getConnection();

        $sql = '
            SELECT * FROM bookings b
            left join payments p
            on b.id = p.booking_id          
            ';

        $stmt = $conn->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }


    /*
    public function findOneBySomeField($value): ?Bookings
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
