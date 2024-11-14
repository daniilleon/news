<?php

namespace Module\Persons\MaritalStatus\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Module\Persons\MaritalStatus\Entity\MaritalStatus;

/**
 * @extends ServiceEntityRepository<MaritalStatus>
 */
class MaritalStatusRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $entityManager;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, MaritalStatus::class);
        $this->entityManager = $entityManager;
    }

    public function saveMaritalStatus(MaritalStatus $maritalStatus, bool $flush = false): void
    {
        $this->entityManager->persist($maritalStatus);
        if ($flush) {
            $this->entityManager->flush();
        }
    }

    public function deleteMaritalStatus(MaritalStatus $maritalStatus, bool $flush = false): void
    {
        $this->entityManager->remove($maritalStatus);
        if ($flush) {
            $this->entityManager->flush();
        }
    }

    public function findMaritalStatusByCode(string $maritalStatusCode): ?MaritalStatus
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.maritalStatusCode = :code')
            ->setParameter('code', $maritalStatusCode)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findAllMaritalStatus(): array
    {
        return $this->findAll() ?: [];
    }

    public function findMaritalStatusById(int $id): ?MaritalStatus
    {
        return $this->find($id);
    }

    public function hasMaritalStatus(): bool
    {
        return !empty($this->findAll());
    }
}
