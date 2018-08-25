<?php

namespace App\Repository;

use App\Entity\MeetingAttendee;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method MeetingAttendee|null find($id, $lockMode = null, $lockVersion = null)
 * @method MeetingAttendee|null findOneBy(array $criteria, array $orderBy = null)
 * @method MeetingAttendee[]    findAll()
 * @method MeetingAttendee[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MeetingAttendeeRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, MeetingAttendee::class);
    }

//    /**
//     * @return MeetingAttendee[] Returns an array of MeetingAttendee objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?MeetingAttendee
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
