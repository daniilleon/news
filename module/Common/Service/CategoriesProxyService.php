<?php

namespace Module\Common\Service;

use Module\Categories\Entity\CategoryTranslations;
use Module\Categories\Repository\CategoriesRepository;
use Module\Categories\Repository\CategoryTranslationsRepository;
use Module\Categories\Service\CategoriesValidationService;
use Psr\Log\LoggerInterface;

class CategoriesProxyService
{
    private CategoriesRepository $categoriesRepository;
    private CategoryTranslationsRepository $translationRepository;
    private CategoriesValidationService $categoriesValidationService;
    private LoggerInterface $logger;

    public function __construct(
        CategoriesRepository $categoriesRepository,
        CategoryTranslationsRepository $translationRepository,
        CategoriesValidationService $categoriesValidationService,
        LoggerInterface $logger,

    ) {
        $this->categoriesRepository = $categoriesRepository;
        $this->translationRepository = $translationRepository;
        $this->categoriesValidationService = $categoriesValidationService;
        $this->logger = $logger;
    }

    /**
     * Получение всех языков.
     */
    public function getAllCategories(): array
    {
        try {
            $this->logger->info("Fetching all languages directly from repository.");
            $languages = $this->categoriesRepository->findAllCategories();

            if (empty($languages)) {
                $this->logger->info("No Categories found in the database.");
                return [];
            }

            return array_map(
                fn($language) => $this->categoriesValidationService->formatCategoryData($language),
                $languages
            );
        } catch (\Exception $e) {
            $this->logger->error("Failed to fetch all Categories: " . $e->getMessage());
            throw new \RuntimeException("Unable to fetch all Categories.");
        }
    }

    /**
     * Получение языка по ID.
     */
    public function getCategoryById(int $categoryId, int $languageId): array
    {
        try {
            $this->logger->info("Fetching Category with ID {$categoryId}.");
            $category = $this->categoriesValidationService->validateCategoryExists($categoryId);

            // Указываем, что не требуется детализированный формат
            return $this->categoriesValidationService->formatCategoryData($category, true, $languageId);
        } catch (\InvalidArgumentException $e) {
            $this->logger->warning("Validation error for Category ID {$categoryId}: " . $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error("Failed to fetch Category with ID {$categoryId}: " . $e->getMessage());
            throw new \RuntimeException("Unable to fetch Category with ID {$categoryId}.");
        }
    }


    /**
     * Валидация существования категории по ID.
     */
    public function validateCategoryExists(mixed $categoryId): array
    {
        try {
            $this->logger->info("Validating Category ID: {$categoryId}");
            $category = $this->categoriesValidationService->validateCategoryExists($categoryId);

            // Получаем отформатированные данные
            $formattedCategory = $this->categoriesValidationService->formatCategoryData($category, true);

            // Проверяем наличие ключа 'CategoryID'
            if (!isset($formattedCategory['Categories']['CategoryID'])) {
                throw new \InvalidArgumentException("Category data does not contain 'CategoryID'.");
            }
            $this->logger->info("Validated category: " . json_encode($formattedCategory));
            return $formattedCategory['Categories'];
        } catch (\InvalidArgumentException $e) {
            $this->logger->warning("Validation failed for Category ID {$categoryId}: {$e->getMessage()}");
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error("Unexpected error during validation for Category ID {$categoryId}: {$e->getMessage()}");
            throw new \RuntimeException("An unexpected error occurred during category validation.");
        }
    }


}
