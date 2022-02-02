<?php

namespace App\Repository;

use App\Entity\Booking;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Booking|null find($id, $lockMode = null, $lockVersion = null)
 * @method Booking|null findOneBy(array $criteria, array $orderBy = null)
 * @method Booking[]    findAll()
 * @method Booking[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BookingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Booking::class);
    }

    public function findByDateWithoutBookings(string $date): array
    {
        $query = $this->createQueryBuilder('b')
            ->select('b.id, b.hour, b.date, u.id as user_id, 
            u.pseudo as user_pseudo, u.email as user_email, 
            c.id as court_id, c.name as court_name, c.surface as court_surface, 
            c.cover as court_cover')
            ->leftJoin('b.user', 'u')
            ->leftJoin('b.court', 'c')
            ->having('b.date = :date')
            ->orderBy('b.id', 'ASC')
            ->setParameter('date', date($date));

        return (array) $query->getQuery()->getResult();
    }

    // /**
    //  * @return Booking[] Returns an array of Booking objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('b.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Booking
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
