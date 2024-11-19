<?php

namespace Module\Employees\EmployeesJobTitle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Module\Employees\EmployeesJobTitle\Entity\EmployeeJobTitleTranslations;
use Module\Employees\EmployeesJobTitle\Entity\EmployeesJobTitle;


/**
 * @extends ServiceEntityRepository<EmployeeJobTitleTranslations>
 */
class EmployeeJobTitleTranslationsRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $entityManager;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, EmployeeJobTitleTranslations::class);
        $this->entityManager = $entityManager;
    }

    public function saveEmployeeJobTitleTranslations(EmployeeJobTitleTranslations $translation, bool $flush = false): void
    {
        $this->entityManager->persist($translation);
        if ($flush) {
            $this->entityManager->flush();
        }
    }

    public function deleteEmployeeJobTitleTranslations(EmployeeJobTitleTranslations $translation, bool $flush = false): void
    {
        $this->entityManager->remove($translation);
        if ($flush) {
            $this->entityManager->flush();
        }
    }

    public function findTranslationsByEmployeesJobTitle(EmployeesJobTitle $employeesJobTitle): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.employeeJobTitleID = :employeeJobTitle')
            ->setParameter('employeeJobTitle', $employeesJobTitle)
            ->orderBy('t.employeeJobTitleName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findTranslationsByEmployeeJobTitleAndLanguage(EmployeesJobTitle $employeesJobTitle, int $languageId): ?EmployeeJobTitleTranslations
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.employeeJobTitleID = :employeeJobTitle')
            ->andWhere('t.languageID = :languageId')
            ->setParameter('employeeJobTitle', $employeesJobTitle)
            ->setParameter('languageId', $languageId)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
