<?php

namespace Module\Core\Categories\Service;

use Module\Core\Categories\Entity\Categories;
use Module\Core\Categories\Entity\CategoryTranslations;
use Module\Core\Categories\Repository\CategoriesRepository;
use Module\Core\Categories\Repository\CategoryTranslationsRepository;
use Module\Common\Helpers\FieldUpdateHelper;
use Module\Common\Service\ImageService;
use Module\Common\Core\LanguagesProxyService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class CategoriesService
{
    private CategoriesRepository $categoryRepository;
    private CategoryTranslationsRepository $translationRepository;
    private LanguagesProxyService $languagesProxyService;
    private CategoriesValidationService $categoriesValidationService;
    private LoggerInterface $logger;
    private ImageService $imageService;
    private FieldUpdateHelper $helper;

    public function __construct(
        CategoriesRepository $categoryRepository,
        CategoryTranslationsRepository $translationRepository,
        LanguagesProxyService $languagesProxyService,
        CategoriesValidationService $categoriesValidationService,
        ImageService $imageService,
        FieldUpdateHelper $helper,
        LoggerInterface $logger
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->translationRepository = $translationRepository;
        $this->languagesProxyService = $languagesProxyService;
        $this->categoriesValidationService = $categoriesValidationService;
        $this->imageService = $imageService;
        $this->helper = $helper;
        $this->logger = $logger;
    }


    /**
     * Получение всех категорий.
     * @return array
     */
    public function getAllCategories(): array
    {
        try {
            $this->logger->info("Executing getAllCategories method.");
            $categories = $this->categoryRepository->findAllCategories();

            // Проверка, есть ли языки
            if (empty($categories)) {
                $this->logger->info("No Categories found in the database.");
                return [
                    'categories' => [],
                    'message' => 'No Categories found in the database.'
                ];
            }
            // Форматируем каждую категорию и добавляем ключ для структурированного ответа
            return [
                'categories' => array_map([$this->categoriesValidationService, 'formatCategoryData'], $categories),
                'message' => 'Categories retrieved successfully.'
            ];
        } catch (\InvalidArgumentException $e) {
            $this->logger->error("Validation error while fetching categories: " . $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error("Unable to fetch languages: " . $e->getMessage());
            throw new \RuntimeException("Unable to fetch categories at the moment.", 0, $e);
        }
    }

    /**
     * Получение категории по ID вместе с переводами.
     *
     * @param int $id
     * @return array|null
     */
    public function getCategoryById(int $id): ?array
    {
        $this->logger->info("Executing getCategoryById method for ID: $id");
        // Используем validateCategoryExists для получения категории или выброса исключения
        $category = $this->categoriesValidationService->validateCategoryExists($id);
        $translations = $this->translationRepository->findTranslationsByCategory($category);
        // Форматируем данные категории и переводов
        return [
            'category' => $this->categoriesValidationService->formatCategoryData($category),
            'translations' => array_map([$this->categoriesValidationService, 'formatCategoryTranslationsData'], $translations),
            'message' => "Category with ID $id retrieved successfully."
        ];
    }

    /**
     * Создание новой категории.
     *
     * @param array $data
     * @return array
     */
    public function createCategory(array $data): array
    {
        $this->logger->info("Executing createCategory method.");
        try {
            // Валидация данных для категории
            $this->categoriesValidationService->validateCategoryLink($data);
            $this->categoriesValidationService->ensureUniqueCategoryLink($data['CategoryLink'] ?? null);

            // Создаем новую категорию
            $category = new Categories();
            $this->helper->validateAndFilterFields($category, $data);//проверяем список разрешенных полей
            $category->setCategoryLink($data['CategoryLink']);

            // Сохраняем категорию в репозитории
            $this->categoryRepository->saveCategory($category, true);
            $this->logger->info("Category '{$category->getCategoryLink()}' created successfully.");

            // Форматируем ответ
            return [
                'category' => $this->categoriesValidationService->formatCategoryData($category),
                'message' => 'Category added successfully.'
            ];
        } catch (\InvalidArgumentException $e) {
            // Логируем ошибку валидации, чтобы избежать дублирования в контроллере
            $this->logger->error("Validation failed for creating category: " . $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            // Логируем общую ошибку и выбрасываем исключение
            $this->logger->error("An unexpected error occurred while creating category: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Добавление перевода для существующей категории.
     *
     * @param int $categoryId
     * @param array $data
     * @return array
     * @throws \InvalidArgumentException Если не удалось найти категорию или язык
     * @throws \Exception Если произошла непредвиденная ошибка
     */
    public function createCategoryTranslation(int $categoryId, array $data): array
    {
        $this->logger->info("Executing createCategoryTranslation method for Category ID: $categoryId.");
        try {
            // Проверяем существование категории
            $category = $this->categoriesValidationService->validateCategoryExists($categoryId);

            // Проверяем наличие выполняем валидацию
            $this->categoriesValidationService->validateCategoryTranslationData($data);
            // Проверяем обязательность поля CategoryName
            $this->categoriesValidationService->ensureUniqueCategoryName($data['CategoryName'] ?? null);
            // Проверка на наличие LanguageID и указать, что это обязательный параметр
            $languageData = $this->languagesProxyService->validateLanguageID($data['LanguageID']  ?? null);

            // Проверка уникальности перевода
            $this->categoriesValidationService->ensureUniqueTranslation($category, $languageData['LanguageID']);

            // Создание нового перевода
            $translation = new CategoryTranslations();
            $this->helper->validateAndFilterFields($translation, $data);//проверяем список разрешенных полей
            $translation->setCategoryID($category);
            $translation->setLanguageID($languageData['LanguageID']);

            // Применение дополнительных полей
            foreach ($data as $field => $value) {
                $setter = 'set' . ucfirst($field);
                if (method_exists($translation, $setter) && !in_array($field, ['CategoryID', 'LanguageID'])) {
                    $translation->$setter($value);
                } elseif (!in_array($field, ['CategoryID', 'LanguageID'])) {
                    throw new \InvalidArgumentException("Field '$field' does not exist on CategoryTranslation entity.");
                }
            }
            $this->helper->validateAndFilterFields($translation, $data);//проверяем список разрешенных полей
            // Сохранение перевода
            $this->translationRepository->saveCategoryTranslations($translation, true);
            $this->logger->info("Translation for Category ID $categoryId created successfully.");

            return [
                'category' => $this->categoriesValidationService->formatCategoryData($category),
                'translation' => $this->categoriesValidationService->formatCategoryTranslationsData($translation),
                'message' => 'Category translation added successfully.'
            ];

        } catch (\InvalidArgumentException $e) {
            // Логируем и передаем исключение с детализированным сообщением
            $this->logger->error("Validation failed for Category ID $categoryId: " . $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            // Обработка неожиданных ошибок
            $this->logger->error("An unexpected error occurred while creating translation for Category ID $categoryId: " . $e->getMessage());
            throw new \RuntimeException("Unable to add category translation", 0, $e);
        }
    }

    public function updateCategoryLink(int $categoryId, array $data): array
    {
        $this->logger->info("Updating category link for Category ID: $categoryId");

        try {
            // Получаем категорию по ID и проверяем ее существование
            $category = $this->categoriesValidationService->validateCategoryExists($categoryId);
            if (!$category) {
                $this->logger->warning("Category with ID $categoryId not found for updating.");
                throw new \InvalidArgumentException("Category with ID $categoryId not found.");
            }

            // Проверка наличия обязательных полей и их значений (в данных или в объекте)
            $requiredFields = ['CategoryLink'];
            foreach ($requiredFields as $field) {
                $value = $data[$field] ?? $category->{'get' . $field}();
                if (empty($value)) {
                    throw new \InvalidArgumentException("Field '$field' is required and cannot be empty.");
                }
            }

            // Используем FieldUpdateHelper для обновления поля CategoryLink с проверками
            FieldUpdateHelper::updateFieldIfPresent(
                $category,
                $data,
                'CategoryLink',
                function ($newLink) use ($categoryId) {
                    $this->categoriesValidationService->ensureUniqueCategoryLink($newLink, $categoryId);
                    $this->categoriesValidationService->validateCategoryLink(['CategoryLink' => $newLink]);
                }
            );

            $this->helper->validateAndFilterFields($category, $data);//проверяем список разрешенных полей
            $this->categoryRepository->saveCategory($category, true);

            $this->logger->info("Category link updated successfully for Category ID: $categoryId");

            return [
                'category' => $this->categoriesValidationService->formatCategoryData($category),
                'message' => 'Category link updated successfully.'
            ];
        } catch (\InvalidArgumentException $e) {
            $this->logger->error("Validation failed for Category ID $categoryId: " . $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error("An unexpected error occurred while updating category link for ID $categoryId: " . $e->getMessage());
            throw new \RuntimeException("Unable to update category link", 0, $e);
        }
    }

    //Обновление OgImage картинки у категории
    public function updateCategoryImage(int $categoryId, ?UploadedFile $file): array
    {
        $this->logger->info("Executing updateCategoryImage method for Category ID: $categoryId.");

        try {
            // Проверяем, существует ли категория
            $category = $this->categoriesValidationService->validateCategoryExists($categoryId);
            $oldImagePath = $category->getOgImage();
            // Загружаем новое изображение и получаем путь
            $newImagePath = $this->imageService->uploadOgImage($file, $categoryId, 'categories', $oldImagePath);
            // Устанавливаем новый путь для изображения
            $category->setOgImage($newImagePath);

            // Сохраняем изменения
            $this->categoryRepository->saveCategory($category, true);
            $this->logger->info("Image for Category ID $categoryId updated successfully.");

            // Возвращаем успешный ответ с новыми данными
            return [
                'category' => $this->categoriesValidationService->formatCategoryData($category),
                'message' => 'Category image updated successfully.'
            ];
        } catch (\InvalidArgumentException $e) {
            // Логируем ошибки валидации и выбрасываем исключение
            $this->logger->error("Validation failed for updating category image: " . $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            // Логируем общую ошибку
            $this->logger->error("An unexpected error occurred while updating category image: " . $e->getMessage());
            throw new \RuntimeException("Unable to update category image at this time.", 0, $e);
        }
    }


    public function updateCategoryTranslation(int $categoryId, int $translationId, array $data): array
    {
        $this->logger->info("Updating translation for Category ID: $categoryId and Translation ID: $translationId");

        try {
            $category = $this->categoriesValidationService->validateCategoryExists($categoryId);

            // Поиск перевода по ID
            $translation = $this->translationRepository->find($translationId);
            if (!$translation || $translation->getCategoryID()->getCategoryID() !== $categoryId) {
                $this->logger->error("Translation for Category ID $categoryId and Translation ID $translationId not found.");
                throw new \InvalidArgumentException("Translation with this ID does not exist for this category.");
            }

            // Проверка, что LanguageID не был передан в запросе
            $this->languagesProxyService->checkImmutableLanguageID($data, $translation->getLanguageID());

            // Проверка наличия обязательных полей и их значений (в данных или в объекте)
            $requiredFields = ['CategoryName'];
            foreach ($requiredFields as $field) {
                $value = $data[$field] ?? $translation->{'get' . $field}();
                if (empty($value)) {
                    throw new \InvalidArgumentException("Field '$field' is required and cannot be empty.");
                }
            }
            // Валидация всех данных, переданных в $data
            $this->categoriesValidationService->validateCategoryTranslationData($data);

            // Обновление поля CategoryName с проверкой уникальности и наличия
            FieldUpdateHelper::updateFieldIfPresent(
                $translation,
                $data,
                'CategoryName',
                function ($newName) use ($translationId) {
                    $this->categoriesValidationService->ensureUniqueCategoryName($newName, $translationId);
                }
            );

            // Обновление полей перевода, игнорируя LanguageID
            foreach ($data as $field => $value) {
                $setter = 'set' . ucfirst($field);
                if (method_exists($translation, $setter) && !in_array($field, ['CategoryID'])) {
                    $translation->$setter($value);
                } elseif (!in_array($field, ['CategoryID'])) {
                    throw new \InvalidArgumentException("Field '$field' does not exist on CategoryTranslation entity.");
                }
            }
            $this->helper->validateAndFilterFields($translation, $data);//проверяем список разрешенных полей
            $this->translationRepository->saveCategoryTranslations($translation, true);
            $this->logger->info("Translation updated successfully for Category ID: $categoryId and Translation ID: $translationId");

            return [
                'category' => $this->categoriesValidationService->formatCategoryData($category),
                'translation' => $this->categoriesValidationService->formatCategoryTranslationsData($translation),
                'message' => 'Category translation updated successfully.'
            ];
        } catch (\InvalidArgumentException $e) {
            $this->logger->error("Validation failed for Category ID $categoryId and Translation ID $translationId: " . $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error("An unexpected error occurred while updating translation for Category ID $categoryId and Translation ID $translationId: " . $e->getMessage());
            throw new \RuntimeException("Unable to update category translation", 0, $e);
        }
    }

    /**
     * Удаление перевода категории.
     *
     * @param int $categoryId
     * @param int $translationId
     * @return array
     */
    public function deleteCategoryTranslation(int $categoryId, int $translationId): array
    {
        $this->logger->info("Deleting translation with ID: $translationId for Category ID: $categoryId");

        try {
            // Проверка существования категории
            $this->categoriesValidationService->validateCategoryExists($categoryId);

            // Проверка существования перевода для данной категории
            $translation = $this->translationRepository->find($translationId);
            if (!$translation || $translation->getCategoryID()->getCategoryID() !== $categoryId) {
                $this->logger->error("Translation with ID $translationId does not exist for Category ID $categoryId.");
                throw new \InvalidArgumentException("Translation with this ID does not exist for this category.");
            }

            // Удаление перевода
            $this->translationRepository->deleteCategoryTranslations($translation, true);
            $this->logger->info("Translation with ID $translationId successfully deleted for Category ID $categoryId.");

            return [
                'message' => "Translation with ID $translationId successfully deleted for Category ID $categoryId.",
            ];
        } catch (\InvalidArgumentException $e) {
            $this->logger->warning($e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error("An unexpected error occurred while deleting translation with ID $translationId for Category ID $categoryId: " . $e->getMessage());
            throw new \RuntimeException("Unable to delete category translation", 0, $e);
        }
    }


    /**
     * Удаление категории.
     *
     * @param int $categoryId
     * @return array
     */
    public function deleteCategory(int $categoryId): array
    {
        try {
            $this->logger->info("Executing deleteCategory method for ID: $categoryId");

            $category = $this->categoriesValidationService->validateCategoryExists($categoryId);


            // Удаляем переводы категории
            $translations = $this->translationRepository->findTranslationsByCategory($category);
            foreach ($translations as $translation) {
                $this->translationRepository->deleteCategoryTranslations($translation, true);
            }

            // Удаляем саму категорию
            $this->categoryRepository->deleteCategory($category, true);
            $this->logger->info("Category with ID $categoryId and its translations successfully deleted.");

            return [
                'message' => "Category with ID $categoryId and its translations successfully deleted.",
            ];
        } catch (\InvalidArgumentException $e) {
            $this->logger->warning($e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error("An unexpected error occurred while deleting category with ID $categoryId: " . $e->getMessage());
            throw $e;
        }
    }


    /*/
    Методы для демо данных
    /*/
    public function seedCategoriesAndTranslations(): array
    {
        $this->logger->info("Executing seedJobTitlesAndTranslations method.");

        // Данные для предустановленных должностей
        $categoriesData = [
            ["CategoryLink" => "first"],
            ["CategoryLink" => "second"]
        ];

        $createdCategories = [];
        $categoryIds = [];

        // Создаём должности и сохраняем их ID
        foreach ($categoriesData as $categoryData) {
            try {
                $this->categoriesValidationService->validateCategoryLink($categoryData);
                $this->categoriesValidationService->ensureUniqueCategoryLink($categoryData['CategoryLink']);

                $category = new Categories();
                $category->setCategoryLink($categoryData['CategoryLink']);
                $this->categoryRepository->saveCategory($category, true);

                $createdCategories[] = $this->categoriesValidationService->formatCategoryData($category);
                $categoryIds[$categoryData['CategoryLink']] = $category->getCategoryID(); // Сохраняем ID должности

                $this->logger->info("Category Link '{$categoryData['CategoryLink']}' created successfully.");
            } catch (\InvalidArgumentException $e) {
                $this->logger->warning("Category Link '{$categoryData['CategoryLink']}' already exists or is invalid. Skipping.");
            }
        }

        // Данные для переводов категорий, привязанные к CategoryID
        $translationsData = [
                $categoryIds['first'] ?? null => [
                    ["CategoryName" => "Первая категория", "CategoryDescription" => "Описание первой категории", "LanguageID" => 2],
                    ["CategoryName" => "First Category", "CategoryDescription" => "First Category Description", "LanguageID" => 1]
                ],
                $categoryIds['second'] ?? null => [
                ["CategoryName" => "Вторая категория", "CategoryDescription" => "Описание первой категории", "LanguageID" => 2],
                ["CategoryName" => "Second Category", "CategoryDescription" => "Second Category Description", "LanguageID" => 1]
            ],
        ];

        $createdTranslations = [];

        // Создаём переводы для каждой должности, используя их ID
        foreach ($translationsData as $categoryIds => $translations) {
            if (!$categoryIds) {
                continue; // Пропускаем, если ID не найден
            }

            $category = $this->categoryRepository->findCategoryById($categoryIds);

            foreach ($translations as $translationData) {
                try {
                    $languageData = $this->languagesProxyService->validateLanguageID($translationData['LanguageID']);
                    $languageId = $languageData['LanguageID'];
                    $this->categoriesValidationService->ensureUniqueTranslation($category, $languageId);

                    $translation = new CategoryTranslations();
                    $translation->setCategoryID($category);
                    $translation->setLanguageID($languageId);
                    $translation->setCategoryName($translationData['CategoryName']);
                    $translation->setCategoryDescription($translationData['CategoryDescription']);

                    $this->translationRepository->saveCategoryTranslations($translation, true);
                    $createdTranslations[] = $this->categoriesValidationService->formatCategoryTranslationsData($translation);

                    $this->logger->info("Translation for Category ID '{$categoryIds}' and LanguageID '{$languageId}' created successfully.");
                } catch (\Exception $e) {
                    $this->logger->error("Failed to add translation for Category ID '$categoryIds': " . $e->getMessage());
                }
            }
        }

        return [
            'categories' => $createdCategories,
            'translations' => $createdTranslations,
            'message' => 'Category and translations seeded successfully.'
        ];
    }
}
