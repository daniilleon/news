<?php

namespace Module\Core\Countries\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Module\Core\Countries\Entity\CountryTranslations;
use Module\Core\Countries\Entity\Countries;

class CountryTranslationsRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $entityManager;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, CountryTranslations::class);
        $this->entityManager = $entityManager;
    }

    public function saveCountryTranslations(CountryTranslations $translation, bool $flush = false): void
    {
        $this->entityManager->persist($translation);
        if ($flush) {
            $this->entityManager->flush();
        }
    }

    public function deleteCountryTranslations(CountryTranslations $translation, bool $flush = false): void
    {
        $this->entityManager->remove($translation);
        if ($flush) {
            $this->entityManager->flush();
        }
    }

    public function findTranslationsByCountry(Countries $country): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.countryID = :country')
            ->setParameter('country', $country)
            ->orderBy('t.countryName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findTranslationsByCountryAndLanguage(Countries $country, int $languageId): ?CountryTranslations
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.countryID = :country')
            ->andWhere('t.languageID = :languageId')
            ->setParameter('country', $country)
            ->setParameter('languageId', $languageId)
            ->getQuery()
            ->getOneOrNullResult();
    }
}