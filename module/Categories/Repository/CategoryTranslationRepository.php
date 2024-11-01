<?php

namespace Module\Categories\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Module\Categories\Entity\CategoryTranslation;
use Module\Categories\Entity\Categories;
use Module\Languages\Entity\Language;

class CategoryTranslationRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $entityManager;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, CategoryTranslation::class);
        $this->entityManager = $entityManager;
    }

    public function saveCategoryTranslation(CategoryTranslation $translation, bool $flush = false): void
    {
        $this->entityManager->persist($translation);
        if ($flush) {
            $this->entityManager->flush();
        }
    }

    public function deleteCategoryTranslation(CategoryTranslation $translation, bool $flush = false): void
    {
        $this->entityManager->remove($translation);
        if ($flush) {
            $this->entityManager->flush();
        }
    }

    public function findTranslationsByCategory(Categories $category): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.categoryID = :category')
            ->setParameter('category', $category)
            ->orderBy('t.categoryName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findTranslationByCategoryAndLanguage(Categories $category, Language $language): ?CategoryTranslation
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.categoryID = :category')
            ->andWhere('t.languageID = :language')
            ->setParameter('category', $category)
            ->setParameter('language', $language)
            ->getQuery()
            ->getOneOrNullResult();
    }

}
