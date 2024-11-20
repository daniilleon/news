<?php

namespace Module\Shared\Industries\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Module\Shared\Industries\Entity\IndustryTranslations;
use Module\Shared\Industries\Entity\Industries;

class IndustryTranslationsRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $entityManager;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, IndustryTranslations::class);
        $this->entityManager = $entityManager;
    }

    public function saveIndustryTranslations(IndustryTranslations $translation, bool $flush = false): void
    {
        $this->entityManager->persist($translation);
        if ($flush) {
            $this->entityManager->flush();
        }
    }

    public function deleteIndustryTranslations(IndustryTranslations $translation, bool $flush = false): void
    {
        $this->entityManager->remove($translation);
        if ($flush) {
            $this->entityManager->flush();
        }
    }

    public function findTranslationsByIndustry(Industries $industry): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.industryID = :industry')
            ->setParameter('industry', $industry)
            ->orderBy('t.industryName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findTranslationByIndustryAndLanguage(Industries $industry, int $languageId): ?IndustryTranslations
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.industryID = :industry')
            ->andWhere('t.languageID = :languageId')
            ->setParameter('industry', $industry)
            ->setParameter('languageId', $languageId)
            ->getQuery()
            ->getOneOrNullResult();
    }
}