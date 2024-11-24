<?php

namespace Module\Shared\MissionsStatements\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Module\Shared\MissionsStatements\Entity\MissionsStatements;

/**
 * @extends ServiceEntityRepository<MissionsStatements>
 */
class MissionsStatementsRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $entityManager;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, MissionsStatements::class);
        $this->entityManager = $entityManager;
    }

    public function saveMissionStatement(MissionsStatements $missionStatement, bool $flush = false): void
    {
        $this->entityManager->persist($missionStatement);
        if ($flush) {
            $this->entityManager->flush();
        }
    }

    public function deleteMissionStatement(MissionsStatements $missionStatement, bool $flush = false): void
    {
        $this->entityManager->remove($missionStatement);
        if ($flush) {
            $this->entityManager->flush();
        }
    }

    public function findMissionStatementByCode(string $missionStatement): ?MissionsStatements
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.missionStatementCode = :code')
            ->setParameter('code', $missionStatement)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findAllMissionsStatements(): array
    {
        return $this->findAll() ?: [];
    }

    public function findMissionStatementById(int $id): ?MissionsStatements
    {
        return $this->find($id);
    }

    public function hasMissionsStatements(): bool
    {
        return !empty($this->findAll());
    }
}
