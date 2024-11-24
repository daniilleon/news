<?php

namespace Module\Shared\Charities\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Module\Shared\Charities\Entity\Charities;

class CharitiesRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $entityManager;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, Charities::class);
        $this->entityManager = $entityManager;
    }

    public function saveCharity(Charities $charity, bool $flush = false): void
    {
        $this->entityManager->persist($charity);
        if ($flush) {
            $this->entityManager->flush();
        }
    }

    public function deleteCharity(Charities $charity, bool $flush = false): void
    {
        $this->entityManager->remove($charity);
        if ($flush) {
            $this->entityManager->flush();
        }
    }

    public function findCharityByLink(string $charityLink): ?Charities
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.charityLink = :link')
            ->setParameter('link', $charityLink)
            ->getQuery()
            ->getOneOrNullResult();
    }


    public function findAllCharities(): array
    {
        return $this->findAll() ?: [];
    }

    public function findCharityById(int $id): ?Charities
    {
        return $this->find($id);
    }

    public function hasCharities(): bool
    {
        return !empty($this->findAll());
    }
}