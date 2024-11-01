<?php

namespace Module\Categories\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Module\Categories\Entity\Categories;

class CategoriesRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $entityManager;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, Categories::class);
        $this->entityManager = $entityManager;
    }

    public function saveCategory(Categories $category, bool $flush = false): void
    {
        $this->entityManager->persist($category);
        if ($flush) {
            $this->entityManager->flush();
        }
    }

    public function deleteCategory(Categories $category, bool $flush = false): void
    {
        $this->entityManager->remove($category);
        if ($flush) {
            $this->entityManager->flush();
        }
    }

//    public function findCategoryByLink(string $categoryLink): ?Categories
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.CategoryLink = :link')
//            ->setParameter('link', $categoryLink)
//            ->getQuery()
//            ->getOneOrNullResult();
//    }

    public function findCategoryByLink(string $categoryLink): ?Categories
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.categoryLink = :link')
            ->setParameter('link', $categoryLink)
            ->getQuery()
            ->getOneOrNullResult();
    }


    public function findAllCategories(): array
    {
        return $this->findAll() ?: [];
    }

    public function findCategoryById(int $id): ?Categories
    {
        return $this->find($id);
    }

    public function hasCategories(): bool
    {
        return !empty($this->findAll());
    }
}