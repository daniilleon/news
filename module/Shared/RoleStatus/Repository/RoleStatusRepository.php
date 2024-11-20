<?php

namespace Module\Shared\RoleStatus\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Module\Shared\RoleStatus\Entity\RoleStatus;

/**
 * @extends ServiceEntityRepository<RoleStatus>
 */
class RoleStatusRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $entityManager;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, RoleStatus::class);
        $this->entityManager = $entityManager;
    }

    public function saveRoleStatus(RoleStatus $roleStatus, bool $flush = false): void
    {
        $this->entityManager->persist($roleStatus);
        if ($flush) {
            $this->entityManager->flush();
        }
    }

    public function deleteRoleStatus(RoleStatus $roleStatus, bool $flush = false): void
    {
        $this->entityManager->remove($roleStatus);
        if ($flush) {
            $this->entityManager->flush();
        }
    }

    public function findRoleStatusByCode(string $roleStatusCode): ?RoleStatus
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.roleStatusCode = :code')
            ->setParameter('code', $roleStatusCode)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findAllRoleStatus(): array
    {
        return $this->findAll() ?: [];
    }

    public function findRoleStatusById(int $id): ?RoleStatus
    {
        return $this->find($id);
    }

    public function hasRoleStatus(): bool
    {
        return !empty($this->findAll());
    }
}
