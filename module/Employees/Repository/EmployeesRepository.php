<?php
namespace Module\Employees\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Module\Employees\Entity\Employee;
use Module\Languages\Entity\Language;

class EmployeesRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $entityManager;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, Employee::class);
        $this->entityManager = $entityManager;
    }

    public function saveEmployee(Employee $employee, bool $flush = false): void
    {
        $this->entityManager->persist($employee);
        if ($flush) {
            $this->entityManager->flush();
        }
    }

    public function deleteEmployee(Employee $employee, bool $flush = false): void
    {
        $this->entityManager->remove($employee);
        if ($flush) {
            $this->entityManager->flush();
        }
    }

    public function findAllEmployees(): array
    {
        return $this->findAll() ?: [];
    }

    public function findEmployeeById(int $id): ?Employee
    {
        return $this->find($id);
    }

    public function findEmployeeByLink(string $employeeLink): ?Employee
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.employeeLink = :employeeLink')
            ->setParameter('employeeLink', $employeeLink)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function hasEmployees(): bool
    {
        return !empty($this->findAll());
    }


    public function findEmployeesByCategory(int $categoryID): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.categoryID = :categoryID')
            ->setParameter('categoryID', $categoryID)
            ->orderBy('e.employeeName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findEmployeesByLanguage(Language $language): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.languageID = :language')
            ->setParameter('language', $language)
            ->orderBy('e.employeeName', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
