<?php

namespace Module\Persons\EducationLevels\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Module\Persons\EducationLevels\Entity\EducationLevels;

/**
 * @extends ServiceEntityRepository<EducationLevels>
 */
class EducationLevelsRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $entityManager;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, EducationLevels::class);
        $this->entityManager = $entityManager;
    }

    public function saveEducationLevels(EducationLevels $educationLevel, bool $flush = false): void
    {
        $this->entityManager->persist($educationLevel);
        if ($flush) {
            $this->entityManager->flush();
        }
    }

    public function deleteEducationLevels(EducationLevels $educationLevel, bool $flush = false): void
    {
        $this->entityManager->remove($educationLevel);
        if ($flush) {
            $this->entityManager->flush();
        }
    }

    public function findEducationLevelByCode(string $educationLevel): ?EducationLevels
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.educationLevelCode = :code')
            ->setParameter('code', $educationLevel)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findAllEducationLevels(): array
    {
        return $this->findAll() ?: [];
    }

    public function findEducationLevelById(int $id): ?EducationLevels
    {
        return $this->find($id);
    }

    public function hasEducationLevels(): bool
    {
        return !empty($this->findAll());
    }
}
