<?php

namespace App\Repository;

use App\Entity\Round;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

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

    public function countAll($userId = null): int
    {
        $queryBuilder = $this->createQueryBuilder('r')
            ->select('count(r.id)');

        $this->filderByUserId($queryBuilder, $userId);
        
            
        return $queryBuilder->getQuery()
            ->getSingleScalarResult();
    }

    public function findAllOf($userId) {
        $queryBuilder = $this->createQueryBuilder('r')
            ->select('r');

        $this->filderByUserId($queryBuilder, $userId);

        return $queryBuilder->getQuery()
            ->getResult();
    }

    public function list($userId, $offset, $limit): array
    {
        $queryBuilder = $this->createQueryBuilder('r')
            ->select('r')
            ->orderBy('r.timestamp', 'desc')
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        $this->filderByUserId($queryBuilder, $userId);

        return $queryBuilder->getQuery()
            ->getResult();
    }

    private function filderByUserId(QueryBuilder $queryBuilder, $userId) {
        if ($userId) {
            $queryBuilder->where('r.userId = :userId')
                ->setParameter('userId', $userId);
        }
    }
}
