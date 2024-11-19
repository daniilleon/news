<?php
namespace Module\Employees\Employees\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Module\Categories\Entity\Categories;
use Module\Employees\Employees\Entity\Employee;
use Module\Employees\EmployeesJobTitle\Entity\EmployeesJobTitle;
use Psr\Log\LoggerInterface;

class EmployeesRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $entityManager;
    private LoggerInterface $logger;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        parent::__construct($registry, Employee::class);
        $this->entityManager = $entityManager;
        $this->logger = $logger;

    }

    public function saveEmployee(Employee $employee, bool $flush = false): void
    {
        $this->entityManager->persist($employee);
        $this->logger->info("Persisting employee with ID: " . $employee->getEmployeeID());
        if ($flush) {
            $this->logger->info("Flushing changes for employee with ID: " . $employee->getEmployeeID());
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


    public function findEmployeesByCategory(int $categoryId): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.categoryID = :categoryId')
            ->setParameter('categoryID', $categoryId)
            ->orderBy('e.employeeName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findEmployeesByEmployeesJobTitle(EmployeesJobTitle $employeesJobTitle): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.employeeJobTitleID = :employeeJobTitle')
            ->setParameter('employeeJobTitleID', $employeesJobTitle)
            ->orderBy('e.employeeName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findEmployeesByLanguage(int $languageId): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.languageID = :languageId')
            ->setParameter('languageID', $languageId)
            ->orderBy('e.employeeName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    //Метод для поиска активных и пассивных сотрудников
    public function findEmployeesByActiveStatus(bool $isActive): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.employeeActive = :active')
            ->setParameter('active', $isActive)
            ->orderBy('e.employeeName', 'ASC')
            ->getQuery()
            ->getResult();
    }

}
