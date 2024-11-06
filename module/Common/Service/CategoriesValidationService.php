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
     * @param array $data
     * @throws \InvalidArgumentException если данные не валидны
     */
    public function validateCategoryLink(array $data): void
    {
        if (!empty($data['CategoryLink']) &&!preg_match('/^[a-zA-Z0-9_-]+$/', $data['CategoryLink'])) {
            $this->logger->error("Invalid characters in CategoryLink.");
            throw new \InvalidArgumentException("Field 'CategoryLink' can contain only letters, numbers, underscores, and hyphens.");
        }
    }

    /**
     * Валидация данных перевода категории.
     */
    public function validateCategoryTranslationData(array $data): void
    {
        // Проверка допустимых символов и длины для CategoryName, если он передан
        if (isset($data['CategoryName'])) {
            $categoryName = $data['CategoryName'];

            // Проверка на допустимые символы и длину
            if (!preg_match('/^[\p{L}0-9 _-]{1,20}$/u', $categoryName)) {
                $this->logger->error("Invalid characters or length in CategoryName.");
                throw new \InvalidArgumentException("Field 'CategoryName' can contain only letters, numbers, underscores, hyphens, spaces, and must be no more than 20 characters long.");
            }

            // Проверка, что CategoryName не состоит только из цифр
            if (preg_match('/^\d+$/', $categoryName)) {
                $this->logger->error("CategoryName cannot consist only of numbers.");
                throw new \InvalidArgumentException("Field 'CategoryName' cannot consist only of numbers.");
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
        if (empty($categoryLink)) {
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
     * Проверка на уникальность CategoryName.
     *
     * @param string array $data
     * @param int|null $excludeId ID категории для исключения (например, при обновлении)
     * @throws \InvalidArgumentException если такой CategoryLink уже существует
     */
    public function ensureUniqueCategoryName(?string $categoryName, ?int $excludeId = null): void
    {
        // Проверка обязательности поля CategoryName
        if (empty($categoryName)) {
            $this->logger->error("CategoryName is required.");
            throw new \InvalidArgumentException("Field 'CategoryName' is required and cannot be empty.");
        }

    }

    /**
     * Получение и проверка существования категории по ID.
     */
    public function validateCategoryExists(mixed $categoryID): Categories
    {
        // Проверка на наличие CategoryID
        if ($categoryID === null) {
            $this->logger->error("Field 'CategoryID' is required.");
            throw new \InvalidArgumentException("Field 'CategoryID' is required.");
        }

        // Проверка на целочисленный тип ID
        if (!is_int($categoryID)) {
            $this->logger->error("Field 'CategoryID' must be an integer.");
            throw new \InvalidArgumentException("Field 'CategoryID' must be an integer.");
        }

        $category = $this->categoryRepository->findCategoryById($categoryID);
        if (!$category) {
            $this->logger->warning("Category with ID $categoryID not found.");
            throw new \InvalidArgumentException("Category with ID $categoryID not found.");
        }
        return $category;
    }

    /**
     * Получение и проверка уникальности перевода.
     */
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
    public function formatCategoryData(Categories $category, bool $detail = false): array
    {
        if($detail) {
            return [
                'Categories' => [
                    'CategoryID' => $category->getCategoryID(),
                    'CategoryLink' => $category->getCategoryLink(),
                ]
            ];
        } else {
            return [
                'CategoryID' => $category->getCategoryID(),
                'CategoryLink' => $category->getCategoryLink(),
            ];
        }
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

    public function formatCategoryMainData(Categories $category, CategoryTranslation $translation, bool $detailed = true): array
    {
        if ($detailed) {
            // Возвращаем полное описание языка
            return [
                'Categories' => [
                    'CategoryID' => $category->getCategoryID(),
                    'CategoryLink' => $category->getCategoryLink(),
                        'Translation' => [
                            'TranslationID' => $translation->getCategoryTranslationID(),
                            'LanguageID' => $translation->getLanguageID()->getLanguageID(),
                            'CategoryName' => $translation->getCategoryName(),
                            'CategoryDescription' => $translation->getCategoryDescription(),
                        ]
                ]
            ];
        } else {
            // Возвращаем только LanguageID как отдельное поле
            return ['CategoryID' => $category->getCategoryID()];
        }
    }

}
