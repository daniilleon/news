<?php

namespace Module\Employees\EmployeesJobTitle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Module\Employees\EmployeesJobTitle\Entity\EmployeesJobTitle;

/**
 * @extends ServiceEntityRepository<EmployeesJobTitle>
 */
class EmployeesJobTitleRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $entityManager;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, EmployeesJobTitle::class);
        $this->entityManager = $entityManager;
    }

    public function saveEmployeeJobTitle(EmployeesJobTitle $employeesJobTitle, bool $flush = false): void
    {
        $this->entityManager->persist($employeesJobTitle);
        if ($flush) {
            $this->entityManager->flush();
        }
    }

    public function deleteEmployeeJobTitle(EmployeesJobTitle $employeesJobTitle, bool $flush = false): void
    {
        $this->entityManager->remove($employeesJobTitle);
        if ($flush) {
            $this->entityManager->flush();
        }
    }

    public function findEmployeeJobTitleByCode(string $employeeJobTitleCode): ?EmployeesJobTitle
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.employeeJobTitleCode = :code')
            ->setParameter('code', $employeeJobTitleCode)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findAllEmployeesJobTitle(): array
    {
        return $this->findAll() ?: [];
    }

    public function findEmployeeJobTitleById(int $id): ?EmployeesJobTitle
    {
        return $this->find($id);
    }

    public function hasEmployeesJobTitle(): bool
    {
        return !empty($this->findAll());
    }
}
