<?php

namespace Module\Countries\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Module\Countries\Entity\Countries;

/**
 * @extends ServiceEntityRepository<Countries>
 */
class CountriesRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $entityManager;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, Countries::class);
        $this->entityManager = $entityManager;
    }

    public function saveCountry(Countries $country, bool $flush = false): void
    {
        $this->entityManager->persist($country);
        if ($flush) {
            $this->entityManager->flush();
        }
    }

    public function deleteCountry(Countries $country, bool $flush = false): void
    {
        $this->entityManager->remove($country);
        if ($flush) {
            $this->entityManager->flush();
        }
    }

    public function findCountryByLink(string $countryLink): ?Countries
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.countryLink = :link')
            ->setParameter('link', $countryLink)
            ->getQuery()
            ->getOneOrNullResult();
    }


    public function findAllCountries(): array
    {
        return $this->findAll() ?: [];
    }

    public function findCountryById(int $id): ?Countries
    {
        return $this->find($id);
    }

    public function hasCountries(): bool
    {
        return !empty($this->findAll());
    }
}
