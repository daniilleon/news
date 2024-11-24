<?php

namespace Module\{{ENTITY_DIR}}\{{ENTITY_NAME}}\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Module\{{ENTITY_DIR}}\{{ENTITY_NAME}}\Entity\{{ENTITY_NAME_ONE}}Translations;
use Module\{{ENTITY_DIR}}\{{ENTITY_NAME}}\Entity\{{ENTITY_NAME}};

/**
 * @extends ServiceEntityRepository<{{ENTITY_NAME_ONE}}Translations>
 */
class {{ENTITY_NAME_ONE}}TranslationsRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $entityManager;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, {{ENTITY_NAME_ONE}}Translations::class);
        $this->entityManager = $entityManager;
    }

    public function save{{ENTITY_NAME_ONE}}Translations({{ENTITY_NAME_ONE}}Translations $translation, bool $flush = false): void
    {
        $this->entityManager->persist($translation);
        if ($flush) {
            $this->entityManager->flush();
        }
    }

    public function delete{{ENTITY_NAME_ONE}}Translations({{ENTITY_NAME_ONE}}Translations $translation, bool $flush = false): void
    {
        $this->entityManager->remove($translation);
        if ($flush) {
            $this->entityManager->flush();
        }
    }

    public function findTranslationsBy{{ENTITY_NAME_ONE}}({{ENTITY_NAME}} ${{ENTITY_NAME_LOWER}}): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.{{ENTITY_NAME_LOWER}}ID = :{{ENTITY_NAME_LOWER}}')
            ->setParameter('{{ENTITY_NAME_LOWER}}', ${{ENTITY_NAME_LOWER}})
            ->orderBy('t.{{ENTITY_NAME_LOWER}}Name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findTranslationBy{{ENTITY_NAME_ONE}}AndLanguage({{ENTITY_NAME}} ${{ENTITY_NAME_LOWER}}, int $languageId): ?{{ENTITY_NAME_ONE}}Translations
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.{{ENTITY_NAME_LOWER}}ID = :{{ENTITY_NAME_LOWER}}')
            ->andWhere('t.languageID = :languageId')
            ->setParameter('{{ENTITY_NAME_LOWER}}', ${{ENTITY_NAME_LOWER}})
            ->setParameter('languageId', $languageId)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
