<?php

namespace Module\Employees\EmployeesJobTitle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Module\Employees\EmployeesJobTitle\Entity\EmployeeJobTitleTranslations;
use Module\Employees\EmployeesJobTitle\Entity\EmployeesJobTitle;
use Module\Languages\Entity\Language;

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

    public function saveEmployeeJobTitleTranslation(EmployeeJobTitleTranslations $translation, bool $flush = false): void
    {
        $this->entityManager->persist($translation);
        if ($flush) {
            $this->entityManager->flush();
        }
    }

    public function deleteEmployeeJobTitleTranslation(EmployeeJobTitleTranslations $translation, bool $flush = false): void
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

    public function findTranslationByEmployeeJobTitleAndLanguage(EmployeesJobTitle $employeesJobTitle, Language $language): ?EmployeeJobTitleTranslations
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.employeeJobTitleID = :employeeJobTitle')
            ->andWhere('t.languageID = :language')
            ->setParameter('employeeJobTitle', $employeesJobTitle)
            ->setParameter('language', $language)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
