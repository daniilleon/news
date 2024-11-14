<?php

namespace Module\Persons\MaritalStatus\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Module\Persons\MaritalStatus\Entity\MaritalStatusTranslations;
use Module\Persons\MaritalStatus\Entity\MaritalStatus;
use Module\Languages\Entity\Language;

/**
 * @extends ServiceEntityRepository<MaritalStatusTranslations>
 */
class MaritalStatusTranslationsRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $entityManager;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, MaritalStatusTranslations::class);
        $this->entityManager = $entityManager;
    }

    public function saveMaritalStatusTranslation(MaritalStatusTranslations $translation, bool $flush = false): void
    {
        $this->entityManager->persist($translation);
        if ($flush) {
            $this->entityManager->flush();
        }
    }

    public function deleteMaritalStatusTranslation(MaritalStatusTranslations $translation, bool $flush = false): void
    {
        $this->entityManager->remove($translation);
        if ($flush) {
            $this->entityManager->flush();
        }
    }

    public function findTranslationsByMaritalStatus(MaritalStatus $maritalStatus): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.maritalStatusID = :maritalStatus')
            ->setParameter('maritalStatus', $maritalStatus)
            ->orderBy('t.maritalStatusName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findTranslationByMaritalStatusAndLanguage(MaritalStatus $maritalStatus, Language $language): ?MaritalStatusTranslations
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.maritalStatusID = :maritalStatus')
            ->andWhere('t.languageID = :language')
            ->setParameter('maritalStatus', $maritalStatus)
            ->setParameter('language', $language)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
