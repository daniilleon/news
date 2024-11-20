<?php

namespace Module\Shared\Industries\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Module\Shared\Industries\Entity\Industries;

class IndustriesRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $entityManager;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, Industries::class);
        $this->entityManager = $entityManager;
    }

    public function saveIndustry(Industries $industry, bool $flush = false): void
    {
        $this->entityManager->persist($industry);
        if ($flush) {
            $this->entityManager->flush();
        }
    }

    public function deleteIndustry(Industries $industry, bool $flush = false): void
    {
        $this->entityManager->remove($industry);
        if ($flush) {
            $this->entityManager->flush();
        }
    }

    public function findIndustryByLink(string $industryLink): ?Industries
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.industryLink = :link')
            ->setParameter('link', $industryLink)
            ->getQuery()
            ->getOneOrNullResult();
    }


    public function findAllIndustries(): array
    {
        return $this->findAll() ?: [];
    }

    public function findIndustryById(int $id): ?Industries
    {
        return $this->find($id);
    }

    public function hasIndustries(): bool
    {
        return !empty($this->findAll());
    }
}