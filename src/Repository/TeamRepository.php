<?php

namespace App\Repository;

use App\Entity\Team;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Team|null find($id, $lockMode = null, $lockVersion = null)
 * @method Team|null findOneBy(array $criteria, array $orderBy = null)
 * @method Team[]    findAll()
 * @method Team[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TeamRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Team::class);
    }

    /**
     * @param User $user
     *
     * @param bool $withoutMyTeams
     * @return Team[]
     */
    public function findUserTeams(User $user, bool $withoutMyTeams = false)
    {
        $qb = $this->createQueryBuilder('t')
            ->leftJoin('t.users', 'u', \Doctrine\ORM\Query\Expr\Join::WITH)
            ->andWhere('u.id = :id')
            ->setParameter('id', $user->getId())
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10);

        if ($withoutMyTeams) {
            $qb
                ->andWhere('t.owner <> :user')
                ->setParameter('user', $user);
        }

        return $qb->getQuery()->getResult();
    }

    /*
    public function findOneBySomeField($value): ?Team
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
