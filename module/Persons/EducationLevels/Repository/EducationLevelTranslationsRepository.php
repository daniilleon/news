<?php

namespace Module\Persons\EducationLevels\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Module\Persons\EducationLevels\Entity\EducationLevelTranslations;
use Module\Persons\EducationLevels\Entity\EducationLevels;

/**
 * @extends ServiceEntityRepository<EducationLevelTranslations>
 */
class EducationLevelTranslationsRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $entityManager;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, EducationLevelTranslations::class);
        $this->entityManager = $entityManager;
    }

    public function saveEducationLevelTranslations(EducationLevelTranslations $translation, bool $flush = false): void
    {
        $this->entityManager->persist($translation);
        if ($flush) {
            $this->entityManager->flush();
        }
    }

    public function deleteEducationLevelTranslations(EducationLevelTranslations $translation, bool $flush = false): void
    {
        $this->entityManager->remove($translation);
        if ($flush) {
            $this->entityManager->flush();
        }
    }

    public function findTranslationsByEducationLevel(EducationLevels $educationLevel): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.educationLevelID = :educationLevel')
            ->setParameter('educationLevel', $educationLevel)
            ->orderBy('t.educationLevelName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findTranslationByEducationLevelAndLanguage(EducationLevels $educationLevel, int $languageId): ?EducationLevelTranslations
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.educationLevelID = :educationLevel')
            ->andWhere('t.languageID = :languageId')
            ->setParameter('educationLevel', $educationLevel)
            ->setParameter('languageId', $languageId)
            ->getQuery()
            ->getOneOrNullResult();
    }
}