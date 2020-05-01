<?php

namespace App\Repository;

use App\Entity\Round;
use App\Entity\Settings;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\ResultSetMapping;

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

    public function getStatistics(): array
    {        
        $statistics = [];

        $settings = $this->getSettingsRepository()->get();
        $statistics['target_amount'] = $settings['target_amount'];

        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('user_id', 'user_id', 'integer');
        $rsm->addScalarResult('rounds', 'rounds', 'integer');
        $rsm->addScalarResult('reward_per_round', 'reward_per_round', 'integer');
        $rsm->addScalarResult('user_amount', 'user_amount', 'integer');

        $query = $this->getEntityManager()->createNativeQuery('
            SELECT
                u.id as user_id,
                COUNT(r.id) as rounds,
                u.reward_per_round as reward_per_round,
                (u.reward_per_round * COUNT(r.id)) as user_amount
            FROM
                user u
            JOIN
                round r ON r.user_id = u.id
            GROUP BY
                u.id
        ', $rsm);

        $userStats = $query->getScalarResult();

        $userIds = array_map(function ($row) { return $row['user_id']; }, $userStats);

            
        $statistics['users'] = array_combine($userIds, $userStats);
        $statistics['current_amount'] = array_reduce($userStats, function ($result, $row) { return $result + $row['user_amount']; }, 0);
        $statistics['missing_amount'] = max(0, $statistics['target_amount'] - $statistics['current_amount']);

        return $statistics;
    }

    public function add(Round $round) {
        $entityManager = $this->getEntityManager();
        $entityManager->persist($round);
        $entityManager->flush();
    }

    public function delete(Round $round) {
        $entityManager = $this->getEntityManager();
        $entityManager->remove($round);
        $entityManager->flush();        
    }

    public function deleteMany(array $rounds) {
        $entityManager = $this->getEntityManager();
        foreach ($rounds as $round) {
            $entityManager->remove($round);
        }
        $entityManager->flush();
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

    private function getSettingsRepository(): SettingsRepository
    {
        return $this->getEntityManager()->getRepository(Settings::class);
    }

}
