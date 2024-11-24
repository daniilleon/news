<?php

namespace Module\Shared\Charities\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Module\Shared\Charities\Entity\CharityTranslations;
use Module\Shared\Charities\Entity\Charities;

class CharityTranslationsRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $entityManager;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, charityTranslations::class);
        $this->entityManager = $entityManager;
    }

    public function saveCharityTranslations(CharityTranslations $translation, bool $flush = false): void
    {
        $this->entityManager->persist($translation);
        if ($flush) {
            $this->entityManager->flush();
        }
    }

    public function deleteCharityTranslations(CharityTranslations $translation, bool $flush = false): void
    {
        $this->entityManager->remove($translation);
        if ($flush) {
            $this->entityManager->flush();
        }
    }

    public function findTranslationsByCharity(Charities $charity): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.charityID = :charity')
            ->setParameter('charity', $charity)
            ->orderBy('t.charityName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findTranslationByCharityAndLanguage(Charities $charity, int $languageId): ?CharityTranslations
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.charityID = :charity')
            ->andWhere('t.languageID = :languageId')
            ->setParameter('charity', $charity)
            ->setParameter('languageId', $languageId)
            ->getQuery()
            ->getOneOrNullResult();
    }
}