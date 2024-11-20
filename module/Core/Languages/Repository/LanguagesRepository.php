<?php

namespace Module\Core\Languages\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Module\Core\Languages\Entity\Language;

class LanguagesRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $entityManager;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, Language::class);
        $this->entityManager = $entityManager;
    }

    /**
     * Сохранение языка в базе данных.
     *
     * @param Language $language
     * @param bool $flush
     * @return void
     */
    public function saveLanguage(Language $language, bool $flush = false): void
    {
        $this->entityManager->persist($language);
        if ($flush) {
            $this->entityManager->flush();
        }
    }

    /**
     * Удаление языка из базы данных.
     *
     * @param Language $language
     * @param bool $flush
     * @return void
     */
    public function delete(Language $language, bool $flush = false): void
    {
        $this->entityManager->remove($language);
        if ($flush) {
            $this->entityManager->flush();
        }
    }

    /**
     * Поиск языка по ID.
     *
     * @param int $id
     * @return Language|null
     */
    public function findLanguageById(int $id): ?Language
    {
        return $this->find($id);
    }

    /**
     * Получение всех языков.
     *
     * @return array
     */
    public function findAllLanguages(): array
    {
        return $this->findAll() ?: [];
    }

    /**
     * Проверка на наличие языков в базе данных.
     *
     * @return bool
     */
    public function hasLanguages(): bool
    {
        return !empty($this->findAll());
    }
}
