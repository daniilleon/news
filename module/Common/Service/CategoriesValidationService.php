<?php

namespace Module\Common\Service;

use Module\Categories\Entity\Categories;
use Module\Categories\Entity\CategoryTranslation;
use Module\Categories\Repository\CategoriesRepository;
use Module\Categories\Repository\CategoryTranslationRepository;
use Module\Languages\Entity\Language;
use Psr\Log\LoggerInterface;

class CategoriesValidationService
{
    private CategoriesRepository $categoryRepository;
    private CategoryTranslationRepository $translationRepository;
    private LoggerInterface $logger;

    public function __construct(
        CategoriesRepository $categoryRepository,
        CategoryTranslationRepository $translationRepository,
        LoggerInterface $logger
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->translationRepository = $translationRepository;
        $this->logger = $logger;
    }

    /**
     * Валидация поля CategoryLink.
     *
     * @param string $categoryLink
     * @param bool $isNew
     * @throws \InvalidArgumentException если данные не валидны
     */
    public function validateCategoryLink(string $categoryLink, bool $isNew = true): void
    {
        if ($isNew && empty(trim($categoryLink))) {
            $this->logger->error("CategoryLink is required.");
            throw new \InvalidArgumentException("Field 'CategoryLink' is required.");
        }

        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $categoryLink)) {
            $this->logger->error("Invalid characters in CategoryLink.");
            throw new \InvalidArgumentException("Field 'CategoryLink' can contain only letters, numbers, underscores, and hyphens.");
        }
    }

    /**
     * Валидация данных перевода категории.
     */
    public function validateCategoryTranslationData(array $data): void
    {
        if (isset($data['CategoryName'])) {
            $categoryName = $data['CategoryName'];
            if (empty(trim($categoryName))) {
                $this->logger->error("CategoryName is required.");
                throw new \InvalidArgumentException("Field 'CategoryName' is required.");
            }
            if (!preg_match('/^[\p{L}0-9 _-]{1,20}$/u', $categoryName)) {
                $this->logger->error("Invalid characters or length in CategoryName.");
                throw new \InvalidArgumentException("Field 'CategoryName' can contain only letters, numbers, underscores, hyphens, spaces, and must be no more than 20 characters long.");
            }
        }

        // Если нужна проверка других полей, добавляем их сюда
        if (isset($data['CategoryDescription']) && strlen($data['CategoryDescription']) > 500) { // пример ограничения
            $this->logger->error("CategoryDescription is too long.");
            throw new \InvalidArgumentException("Field 'CategoryDescription' cannot exceed 500 characters.");
        }
    }

    /**
     * Проверка на уникальность CategoryLink.
     *
     * @param string $categoryLink
     * @param int|null $excludeId ID категории для исключения (например, при обновлении)
     * @throws \InvalidArgumentException если такой CategoryLink уже существует
     */
    public function ensureUniqueCategoryLink(?string $categoryLink, ?int $excludeId = null): void
    {
        // Проверка, что CategoryLink передан
        if ($categoryLink === null) {
            $this->logger->error("Field 'CategoryLink' is required.");
            throw new \InvalidArgumentException("Field 'CategoryLink' is required.");
        }

        // Проверка на уникальность CategoryLink
        $existingCategory = $this->categoryRepository->findCategoryByLink($categoryLink);
        if ($existingCategory && ($excludeId === null || $existingCategory->getCategoryID() !== $excludeId)) {
            $this->logger->error("Duplicate CategoryLink found: " . $categoryLink);
            throw new \InvalidArgumentException("CategoryLink '{$categoryLink}' already exists.");
        }
    }

    /**
     * Получение и проверка существования категории по ID.
     *
     * @param int $id
     * @return Categories
     * @throws \InvalidArgumentException если категория не найдена
     */
    public function validateCategoryExists(int $id): Categories
    {
        $category = $this->categoryRepository->findCategoryById($id);
        if (!$category) {
            $this->logger->warning("Category with ID $id not found.");
            throw new \InvalidArgumentException("Category with ID $id not found.");
        }
        return $category;
    }

    public function ensureUniqueTranslation(Categories $category, Language $language): void
    {
        $existingTranslation = $this->translationRepository->findTranslationByCategoryAndLanguage($category, $language);
        if ($existingTranslation) {
            $this->logger->error("Translation for Category ID {$category->getCategoryID()} with Language ID {$language->getLanguageID()} already exists.");
            throw new \InvalidArgumentException("Translation for this language already exists for this category.");
        }
    }

    /**
     * Форматирование данных категории для ответа.
     *
     * @param Categories $category
     * @return array
     */
    public function formatCategoryData(Categories $category): array
    {
        return [
            'CategoryID' => $category->getCategoryID(),
            'CategoryLink' => $category->getCategoryLink(),
            'CreatedDate' => $category->getCreatedDate(),
        ];
    }

    /**
     * Форматирование данных перевода категории для ответа.
     *
     * @param CategoryTranslation $translation
     * @return array
     */
    public function formatCategoryTranslationData(CategoryTranslation $translation): array
    {
        return [
            'TranslationID' => $translation->getCategoryTranslationID(),
            'LanguageID' => $translation->getLanguageID()->getLanguageID(),
            'CategoryName' => $translation->getCategoryName(),
            'CategoryDescription' => $translation->getCategoryDescription(),
        ];
    }
}
