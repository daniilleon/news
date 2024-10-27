<?php
namespace Module\Languages\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Module\Languages\Entity\Language;

class LanguageRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $entityManager;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, Language::class);
        $this->entityManager = $entityManager;
    }

    // Сохранение языка в базе данных
    public function save(Language $language, bool $flush = false): void
    {
        $this->entityManager->persist($language);
        if ($flush) {
            $this->entityManager->flush();
        }
    }

    // Удаление языка по ID
    public function delete(Language $language, bool $flush = false): void
    {
        $this->entityManager->remove($language);
        if ($flush) {
            $this->entityManager->flush();
        }
    }

    // Поиск языка по ID
    public function findLanguageById(int $id): ?Language
    {
        return $this->find($id);
    }

    // Получение всех языков
    public function findAllLanguages(): array
    {
        $languages = $this->findAll();
        return $languages ?: []; // Возвращаем пустой массив, если языков нет
    }

    // Проверка на наличие языков
    public function hasLanguages(): bool
    {
        return !empty($this->findAll());
    }

    // Обновление языка по ID
    public function updateLanguage(int $id, array $data): ?Language
    {
        // Находим язык по ID
        $language = $this->find($id);

        // Если язык не найден, возвращаем null
        if (!$language) {
            return null;
        }

        // Обновляем поля на основе входных данных
        if (isset($data['name'])) {
            $language->setName($data['name']);
        }

        if (isset($data['code'])) {
            $language->setCode($data['code']);
        }
        // Добавляем любые другие поля, которые нужно обновлять

        // Сохраняем изменения в базе данных
        $this->entityManager->persist($language);
        $this->entityManager->flush();

        return $language;
    }


}
