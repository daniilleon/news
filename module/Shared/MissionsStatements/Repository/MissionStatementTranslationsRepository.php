<?php

namespace Module\Shared\MissionsStatements\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Module\Shared\MissionsStatements\Entity\MissionStatementTranslations;
use Module\Shared\MissionsStatements\Entity\MissionsStatements;

/**
 * @extends ServiceEntityRepository<MissionStatementTranslations>
 */
class MissionStatementTranslationsRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $entityManager;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, MissionStatementTranslations::class);
        $this->entityManager = $entityManager;
    }

    public function saveMissionStatementTranslations(MissionStatementTranslations $translation, bool $flush = false): void
    {
        $this->entityManager->persist($translation);
        if ($flush) {
            $this->entityManager->flush();
        }
    }

    public function deleteMissionStatementTranslations(MissionStatementTranslations $translation, bool $flush = false): void
    {
        $this->entityManager->remove($translation);
        if ($flush) {
            $this->entityManager->flush();
        }
    }

    public function findTranslationsByMissionStatement(MissionsStatements $missionStatement): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.missionStatementID = :missionStatement')
            ->setParameter('missionStatement', $missionStatement)
            ->orderBy('t.missionStatementName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findTranslationByMissionStatementAndLanguage(MissionsStatements $missionStatement, int $languageId): ?MissionStatementTranslations
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.missionStatementID = :missionStatement')
            ->andWhere('t.languageID = :languageId')
            ->setParameter('missionStatement', $missionStatement)
            ->setParameter('languageId', $languageId)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
