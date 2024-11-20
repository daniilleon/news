<?php

namespace Module\Shared\RoleStatus\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Module\Shared\RoleStatus\Entity\RoleStatusTranslations;
use Module\Shared\RoleStatus\Entity\RoleStatus;

/**
 * @extends ServiceEntityRepository<RoleStatusTranslations>
 */
class RoleStatusTranslationsRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $entityManager;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, RoleStatusTranslations::class);
        $this->entityManager = $entityManager;
    }

    public function saveRoleStatusTranslations(RoleStatusTranslations $translation, bool $flush = false): void
    {
        $this->entityManager->persist($translation);
        if ($flush) {
            $this->entityManager->flush();
        }
    }

    public function deleteRoleStatusTranslations(RoleStatusTranslations $translation, bool $flush = false): void
    {
        $this->entityManager->remove($translation);
        if ($flush) {
            $this->entityManager->flush();
        }
    }

    public function findTranslationsByRoleStatus(RoleStatus $roleStatus): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.roleStatusID = :roleStatus')
            ->setParameter('roleStatus', $roleStatus)
            ->orderBy('t.roleStatusName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findTranslationByRoleStatusAndLanguage(RoleStatus $roleStatus, int $languageId): ?RoleStatusTranslations
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.roleStatusID = :roleStatus')
            ->andWhere('t.languageID = :languageId')
            ->setParameter('roleStatus', $roleStatus)
            ->setParameter('languageId', $languageId)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
