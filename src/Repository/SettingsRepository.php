<?php

namespace App\Repository;

use App\Entity\Settings;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Settings|null find($id, $lockMode = null, $lockVersion = null)
 * @method Settings|null findOneBy(array $criteria, array $orderBy = null)
 * @method Settings[]    findAll()
 * @method Settings[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SettingsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Settings::class);
    }

    public function get(): array
    {
        return $this->getEntity()->getSettings();
    }

    public function set(array $settings) {
        $entity = $this->getEntity();
        $entity->setSettings($settings);
        $this->getEntityManager()->flush();
    }

    private function getEntity(): Settings
    {
        $queryBuilder = $this->createQueryBuilder('s')
            ->select('s');

        $queryBuilder->setMaxResults(1);
        $queryBuilder->setFirstResult(0);

        $settings = $queryBuilder->getQuery()
            ->getOneOrNullResult();

        if (!$settings) {
            $settings = new Settings();
            $entityManager = $this->getEntityManager();
            $entityManager->persist($settings);
            $entityManager->flush();
        }

        return $settings;
    }
}
