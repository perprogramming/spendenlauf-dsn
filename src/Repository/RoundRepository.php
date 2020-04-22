<?php

namespace App\Repository;

use App\Entity\Round;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\User;

/**
 * @method Round|null find($id, $lockMode = null, $lockVersion = null)
 * @method Round|null findOneBy(array $criteria, array $orderBy = null)
 * @method Round[]    findAll()
 * @method Round[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RoundRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Round::class);
    }

    public function countAll(): int
    {
        return $this->createQueryBuilder('r')
            ->select('count(r.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countByUser(User $user): int
    {
        return $this->createQueryBuilder('r')
            ->select('count(r.id)')
            ->where('r.userId = :userId')
            ->setParameter('userId', $user->getId())
            ->getQuery()
            ->getSingleScalarResult();
    }
}
