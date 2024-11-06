<?php

namespace Module\Countries\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Module\Countries\Entity\CountryTranslation;
use Module\Countries\Entity\Countries;
use Module\Languages\Entity\Language;

/**
 * @extends ServiceEntityRepository<CountryTranslation>
 */
class CountryTranslationRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $entityManager;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, CountryTranslation::class);
        $this->entityManager = $entityManager;
    }

    public function saveCountryTranslation(CountryTranslation $translation, bool $flush = false): void
    {
        $this->entityManager->persist($translation);
        if ($flush) {
            $this->entityManager->flush();
        }
    }

    public function deleteCountryTranslation(CountryTranslation $translation, bool $flush = false): void
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

    public function findTranslationByCountryAndLanguage(Countries $country, Language $language): ?CountryTranslation
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.countryID = :country')
            ->andWhere('t.languageID = :language')
            ->setParameter('country', $country)
            ->setParameter('language', $language)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
