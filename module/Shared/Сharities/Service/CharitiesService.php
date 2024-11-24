<?php

namespace Module\Shared\Charities\Service;

use Module\Shared\Charities\Entity\Charities;
use Module\Shared\Charities\Entity\CharityTranslations;
use Module\Shared\Charities\Repository\CharitiesRepository;
use Module\Shared\Charities\Repository\CharityTranslationsRepository;
use Module\Common\Helpers\FieldUpdateHelper;
use Module\Common\Service\ImageService;
use Module\Common\Proxy\Core\LanguagesProxyService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class CharitiesService
{
    private CharitiesRepository $charityRepository;
    private CharityTranslationsRepository $translationRepository;
    private LanguagesProxyService $languagesProxyService;
    private CharitiesValidationService $charitiesValidationService;
    private LoggerInterface $logger;
    private ImageService $imageService;
    private FieldUpdateHelper $helper;

    public function __construct(
        CharitiesRepository $charityRepository,
        CharityTranslationsRepository $translationRepository,
        LanguagesProxyService $languagesProxyService,
        CharitiesValidationService $charitiesValidationService,
        ImageService $imageService,
        FieldUpdateHelper $helper,
        LoggerInterface $logger
    ) {
        $this->charityRepository = $charityRepository;
        $this->translationRepository = $translationRepository;
        $this->languagesProxyService = $languagesProxyService;
        $this->charitiesValidationService = $charitiesValidationService;
        $this->imageService = $imageService;
        $this->helper = $helper;
        $this->logger = $logger;
    }


    /**
     * Получение всех категорий.
     * @return array
     */
    public function getAllCharities(): array
    {
        try {
            $this->logger->info("Executing getAllCharities method.");
            $charities = $this->charityRepository->findAllCharities();

            // Проверка, есть ли языки
            if (empty($charities)) {
                $this->logger->info("No Charities found in the database.");
                return [
                    'charities' => [],
                    'message' => 'No Charities found in the database.'
                ];
            }
            // Форматируем каждую категорию и добавляем ключ для структурированного ответа
            return [
                'charities' => array_map([$this->charitiesValidationService, 'formatCharityData'], $charities),
                'message' => 'Charities retrieved successfully.'
            ];
        } catch (\InvalidArgumentException $e) {
            $this->logger->error("Validation error while fetching Charities: " . $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error("Unable to fetch languages: " . $e->getMessage());
            throw new \RuntimeException("Unable to fetch Charities at the moment.", 0, $e);
        }
    }

    /**
     * Получение категории по ID вместе с переводами.
     *
     * @param int $id
     * @return array|null
     */
    public function getCharityById(int $id): ?array
    {
        $this->logger->info("Executing getCharityById method for ID: $id");
        // Используем validateCharityExists для получения категории или выброса исключения
        $charity = $this->charitiesValidationService->validateCharityExists($id);
        $translations = $this->translationRepository->findTranslationsByCharity($charity);
        // Форматируем данные категории и переводов
        return [
            'charity' => $this->charitiesValidationService->formatCharityData($charity),
            'translations' => array_map([$this->charitiesValidationService, 'formatCharityTranslationsData'], $translations),
            'message' => "Charity with ID $id retrieved successfully."
        ];
    }

    /**
     * Создание новой категории.
     *
     * @param array $data
     * @return array
     */
    public function createCharity(array $data): array
    {
        $this->logger->info("Executing createCharity method.");
        try {
            // Валидация данных для категории
            $this->charitiesValidationService->validateCharityLink($data);
            $this->charitiesValidationService->ensureUniqueCharityLink($data['CharityLink'] ?? null);

            // Создаем новую категорию
            $charity = new Charities();
            $this->helper->validateAndFilterFields($charity, $data);//проверяем список разрешенных полей
            $charity->setCharityLink($data['CharityLink']);

            // Сохраняем категорию в репозитории
            $this->charityRepository->saveCharity($charity, true);
            $this->logger->info("Charity '{$charity->getCharityLink()}' created successfully.");

            // Форматируем ответ
            return [
                'charity' => $this->charitiesValidationService->formatCharityData($charity),
                'message' => 'Charity added successfully.'
            ];
        } catch (\InvalidArgumentException $e) {
            // Логируем ошибку валидации, чтобы избежать дублирования в контроллере
            $this->logger->error("Validation failed for creating charity: " . $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            // Логируем общую ошибку и выбрасываем исключение
            $this->logger->error("An unexpected error occurred while creating charity: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Добавление перевода для существующей категории.
     *
     * @param int $charityId
     * @param array $data
     * @return array
     * @throws \InvalidArgumentException Если не удалось найти категорию или язык
     * @throws \Exception Если произошла непредвиденная ошибка
     */
    public function createCharityTranslation(int $charityId, array $data): array
    {
        $this->logger->info("Executing createCharityTranslation method for Charity ID: $charityId.");
        try {
            // Проверяем существование категории
            $charity = $this->charitiesValidationService->validateCharityExists($charityId);

            // Проверяем наличие выполняем валидацию
            $this->charitiesValidationService->validateCharityTranslationData($data);
            // Проверяем обязательность поля CharityName
            $this->charitiesValidationService->ensureUniqueCharityName($data['CharityName'] ?? null);
            // Проверка на наличие LanguageID и указать, что это обязательный параметр
            $languageData = $this->languagesProxyService->validateLanguageID($data['LanguageID']  ?? null);

            // Проверка уникальности перевода
            $this->charitiesValidationService->ensureUniqueTranslation($charity, $languageData['LanguageID']);

            // Создание нового перевода
            $translation = new CharityTranslations();
            $this->helper->validateAndFilterFields($translation, $data);//проверяем список разрешенных полей
            $translation->setCharityID($charity);
            $translation->setLanguageID($languageData['LanguageID']);

            // Применение дополнительных полей
            foreach ($data as $field => $value) {
                $setter = 'set' . ucfirst($field);
                if (method_exists($translation, $setter) && !in_array($field, ['CharityID', 'LanguageID'])) {
                    $translation->$setter($value);
                } elseif (!in_array($field, ['CharityID', 'LanguageID'])) {
                    throw new \InvalidArgumentException("Field '$field' does not exist on CharityTranslation entity.");
                }
            }
            $this->helper->validateAndFilterFields($translation, $data);//проверяем список разрешенных полей
            // Сохранение перевода
            $this->translationRepository->saveCharityTranslations($translation, true);
            $this->logger->info("Translation for Charity ID $charityId created successfully.");

            return [
                'charity' => $this->charitiesValidationService->formatCharityData($charity),
                'translation' => $this->charitiesValidationService->formatCharityTranslationsData($translation),
                'message' => 'Charity translation added successfully.'
            ];

        } catch (\InvalidArgumentException $e) {
            // Логируем и передаем исключение с детализированным сообщением
            $this->logger->error("Validation failed for Charity ID $charityId: " . $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            // Обработка неожиданных ошибок
            $this->logger->error("An unexpected error occurred while creating translation for Charity ID $charityId: " . $e->getMessage());
            throw new \RuntimeException("Unable to add charity translation", 0, $e);
        }
    }

    public function updateCharityLink(int $charityId, array $data): array
    {
        $this->logger->info("Updating charity link for charity ID: $charityId");

        try {
            // Получаем категорию по ID и проверяем ее существование
            $charity = $this->charitiesValidationService->validateCharityExists($charityId);
            if (!$charity) {
                $this->logger->warning("Charity with ID $charityId not found for updating.");
                throw new \InvalidArgumentException("Charity with ID $charityId not found.");
            }

            // Проверка наличия обязательных полей и их значений (в данных или в объекте)
            $requiredFields = ['CharityLink'];
            foreach ($requiredFields as $field) {
                $value = $data[$field] ?? $charity->{'get' . $field}();
                if (empty($value)) {
                    throw new \InvalidArgumentException("Field '$field' is required and cannot be empty.");
                }
            }

            // Используем FieldUpdateHelper для обновления поля CharityLink с проверками
            FieldUpdateHelper::updateFieldIfPresent(
                $charity,
                $data,
                'CharityLink',
                function ($newLink) use ($charityId) {
                    $this->charitiesValidationService->ensureUniqueCharityLink($newLink, $charityId);
                    $this->charitiesValidationService->validateCharityLink(['CharityLink' => $newLink]);
                }
            );

            $this->helper->validateAndFilterFields($charity, $data);//проверяем список разрешенных полей
            $this->charityRepository->saveCharity($charity, true);

            $this->logger->info("Charity link updated successfully for Charity ID: $charityId");

            return [
                'charity' => $this->charitiesValidationService->formatCharityData($charity),
                'message' => 'Charity link updated successfully.'
            ];
        } catch (\InvalidArgumentException $e) {
            $this->logger->error("Validation failed for Charity ID $charityId: " . $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error("An unexpected error occurred while updating charity link for ID $charityId: " . $e->getMessage());
            throw new \RuntimeException("Unable to update charity link", 0, $e);
        }
    }

    //Обновление OgImage картинки у категории
    public function updateCharityImage(int $charityId, ?UploadedFile $file): array
    {
        $this->logger->info("Executing updateCharityImage method for Charity ID: $charityId.");

        try {
            // Проверяем, существует ли категория
            $charity = $this->charitiesValidationService->validateCharityExists($charityId);
            $oldImagePath = $charity->getOgImage();
            // Загружаем новое изображение и получаем путь
            $newImagePath = $this->imageService->uploadOgImage($file, $charityId, 'charities', $oldImagePath);
            // Устанавливаем новый путь для изображения
            $charity->setOgImage($newImagePath);

            // Сохраняем изменения
            $this->charityRepository->saveCharity($charity, true);
            $this->logger->info("Image for Charity ID $charityId updated successfully.");

            // Возвращаем успешный ответ с новыми данными
            return [
                'charity' => $this->charitiesValidationService->formatCharityData($charity),
                'message' => 'Charity image updated successfully.'
            ];
        } catch (\InvalidArgumentException $e) {
            // Логируем ошибки валидации и выбрасываем исключение
            $this->logger->error("Validation failed for updating charity image: " . $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            // Логируем общую ошибку
            $this->logger->error("An unexpected error occurred while updating charity image: " . $e->getMessage());
            throw new \RuntimeException("Unable to update charity image at this time.", 0, $e);
        }
    }


    public function updateCharityTranslation(int $charityId, int $translationId, array $data): array
    {
        $this->logger->info("Updating translation for Charity ID: $charityId and Translation ID: $translationId");

        try {
            $charity = $this->charitiesValidationService->validateCharityExists($charityId);

            // Поиск перевода по ID
            $translation = $this->translationRepository->find($translationId);
            if (!$translation || $translation->getCharityID()->getCharityID() !== $charityId) {
                $this->logger->error("Translation for charity ID $charityId and Translation ID $translationId not found.");
                throw new \InvalidArgumentException("Translation with this ID does not exist for this charity.");
            }

            // Проверка, что LanguageID не был передан в запросе
            $this->languagesProxyService->checkImmutableLanguageID($data, $translation->getLanguageID());

            // Проверка наличия обязательных полей и их значений (в данных или в объекте)
            $requiredFields = ['CharityName'];
            foreach ($requiredFields as $field) {
                $value = $data[$field] ?? $translation->{'get' . $field}();
                if (empty($value)) {
                    throw new \InvalidArgumentException("Field '$field' is required and cannot be empty.");
                }
            }
            // Валидация всех данных, переданных в $data
            $this->charitiesValidationService->validateCharityTranslationData($data);

            // Обновление поля CharityName с проверкой уникальности и наличия
            FieldUpdateHelper::updateFieldIfPresent(
                $translation,
                $data,
                'CharityName',
                function ($newName) use ($translationId) {
                    $this->charitiesValidationService->ensureUniqueCharityName($newName, $translationId);
                }
            );

            // Обновление полей перевода, игнорируя LanguageID
            foreach ($data as $field => $value) {
                $setter = 'set' . ucfirst($field);
                if (method_exists($translation, $setter) && !in_array($field, ['CharityID'])) {
                    $translation->$setter($value);
                } elseif (!in_array($field, ['CharityID'])) {
                    throw new \InvalidArgumentException("Field '$field' does not exist on CharityTranslation entity.");
                }
            }
            $this->helper->validateAndFilterFields($translation, $data);//проверяем список разрешенных полей
            $this->translationRepository->saveCharityTranslations($translation, true);
            $this->logger->info("Translation updated successfully for Charity ID: $charityId and Translation ID: $translationId");

            return [
                'charity' => $this->charitiesValidationService->formatCharityData($charity),
                'translation' => $this->charitiesValidationService->formatCharityTranslationsData($translation),
                'message' => 'Charity translation updated successfully.'
            ];
        } catch (\InvalidArgumentException $e) {
            $this->logger->error("Validation failed for Charity ID $charityId and Translation ID $translationId: " . $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error("An unexpected error occurred while updating translation for charity ID $charityId and Translation ID $translationId: " . $e->getMessage());
            throw new \RuntimeException("Unable to update charity translation", 0, $e);
        }
    }

    /**
     * Удаление перевода категории.
     *
     * @param int $charityId
     * @param int $translationId
     * @return array
     */
    public function deleteCharityTranslation(int $charityId, int $translationId): array
    {
        $this->logger->info("Deleting translation with ID: $translationId for Charity ID: $charityId");

        try {
            // Проверка существования категории
            $this->charitiesValidationService->validateCharityExists($charityId);

            // Проверка существования перевода для данной категории
            $translation = $this->translationRepository->find($translationId);
            if (!$translation || $translation->getCharityID()->getCharityID() !== $charityId) {
                $this->logger->error("Translation with ID $translationId does not exist for Charity ID $charityId.");
                throw new \InvalidArgumentException("Translation with this ID does not exist for this charity.");
            }

            // Удаление перевода
            $this->translationRepository->deleteCharityTranslations($translation, true);
            $this->logger->info("Translation with ID $translationId successfully deleted for Charity ID $charityId.");

            return [
                'message' => "Translation with ID $translationId successfully deleted for Charity ID $charityId.",
            ];
        } catch (\InvalidArgumentException $e) {
            $this->logger->warning($e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error("An unexpected error occurred while deleting translation with ID $translationId for Charity ID $charityId: " . $e->getMessage());
            throw new \RuntimeException("Unable to delete charity translation", 0, $e);
        }
    }


    /**
     * Удаление категории.
     *
     * @param int $charityId
     * @return array
     */
    public function deleteCharity(int $charityId): array
    {
        try {
            $this->logger->info("Executing deleteCharity method for ID: $charityId");

            $charity = $this->charitiesValidationService->validateCharityExists($charityId);


            // Удаляем переводы категории
            $translations = $this->translationRepository->findTranslationsByCharity($charity);
            foreach ($translations as $translation) {
                $this->translationRepository->deleteCharityTranslations($translation, true);
            }

            // Удаляем саму категорию
            $this->charityRepository->deleteCharity($charity, true);
            $this->logger->info("Charity with ID $charityId and its translations successfully deleted.");

            return [
                'message' => "Charity with ID $charityId and its translations successfully deleted.",
            ];
        } catch (\InvalidArgumentException $e) {
            $this->logger->warning($e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error("An unexpected error occurred while deleting charity with ID $charityId: " . $e->getMessage());
            throw $e;
        }
    }


    /*/
    Методы для демо данных
    /*/
    public function seedCharitiesAndTranslations(): array
    {
        $this->logger->info("Executing seedJobTitlesAndTranslations method.");

        // Данные для предустановленных типов индустрий
        $charitiesData = [
            ["CharityLink" => "technology"],
            ["CharityLink" => "finance"],
            ["CharityLink" => "healthcare"],
            ["CharityLink" => "real_estate"],
            ["CharityLink" => "energy"],
            ["CharityLink" => "education"],
            ["CharityLink" => "transportation"],
            ["CharityLink" => "retail"],
            ["CharityLink" => "media"],
            ["CharityLink" => "agriculture"],
            ["CharityLink" => "automotive"],
            ["CharityLink" => "entertainment"],
            ["CharityLink" => "manufacturing"],
            ["CharityLink" => "hospitality"],
            ["CharityLink" => "other"]
        ];


        $createdCharities = [];
        $charityIds = [];

        // Создаём должности и сохраняем их ID
        foreach ($charitiesData as $charityData) {
            try {
                $this->charitiesValidationService->validateCharityLink($charityData);
                $this->charitiesValidationService->ensureUniqueCharityLink($charityData['CharityLink']);

                $charity = new Charities();
                $charity->setCharityLink($charityData['CharityLink']);
                $this->charityRepository->saveCharity($charity, true);

                $createdCharities[] = $this->charitiesValidationService->formatCharityData($charity);
                $charityIds[$charityData['CharityLink']] = $charity->getCharityID(); // Сохраняем ID должности

                $this->logger->info("Charity Link '{$charityData['CharityLink']}' created successfully.");
            } catch (\InvalidArgumentException $e) {
                $this->logger->warning("Charity Link '{$charityData['CharityLink']}' already exists or is invalid. Skipping.");
            }
        }

        // Данные для переводов Индустрии, привязанные к CharityID
        $translationsData = [
                $charityIds['technology'] ?? null => [
                ["CharityName" => "Технологии", "CharityDescription" => "Все отрасли, связанные с информационными технологиями", "LanguageID" => 2],
                ["CharityName" => "Technology", "CharityDescription" => "All technology-related Charities", "LanguageID" => 1]
            ],
                $charityIds['finance'] ?? null => [
                ["CharityName" => "Финансы", "CharityDescription" => "Финансовые услуги и рынки", "LanguageID" => 2],
                ["CharityName" => "Finance", "CharityDescription" => "Financial services and markets", "LanguageID" => 1]
            ],
                $charityIds['healthcare'] ?? null => [
                ["CharityName" => "Здравоохранение", "CharityDescription" => "Услуги и технологии в здравоохранении", "LanguageID" => 2],
                ["CharityName" => "Healthcare", "CharityDescription" => "Healthcare services and technologies", "LanguageID" => 1]
            ],
                $charityIds['real_estate'] ?? null => [
                ["CharityName" => "Недвижимость", "CharityDescription" => "Строительство и продажа недвижимости", "LanguageID" => 2],
                ["CharityName" => "Real Estate", "CharityDescription" => "Construction and real estate sales", "LanguageID" => 1]
            ],
                $charityIds['energy'] ?? null => [
                ["CharityName" => "Энергетика", "CharityDescription" => "Отрасли, связанные с энергией и ресурсами", "LanguageID" => 2],
                ["CharityName" => "Energy", "CharityDescription" => "Energy and resource-related Charities", "LanguageID" => 1]
            ],
                $charityIds['education'] ?? null => [
                ["CharityName" => "Образование", "CharityDescription" => "Услуги и технологии в образовании", "LanguageID" => 2],
                ["CharityName" => "Education", "CharityDescription" => "Education services and technologies", "LanguageID" => 1]
            ],
                $charityIds['transportation'] ?? null => [
                ["CharityName" => "Транспорт", "CharityDescription" => "Перевозки и логистика", "LanguageID" => 2],
                ["CharityName" => "Transportation", "CharityDescription" => "Transportation and logistics", "LanguageID" => 1]
            ],
                $charityIds['retail'] ?? null => [
                ["CharityName" => "Розничная торговля", "CharityDescription" => "Продажа товаров и услуг", "LanguageID" => 2],
                ["CharityName" => "Retail", "CharityDescription" => "Goods and services retail", "LanguageID" => 1]
            ],
                $charityIds['media'] ?? null => [
                ["CharityName" => "Медиа", "CharityDescription" => "Медиа и развлекательные услуги", "LanguageID" => 2],
                ["CharityName" => "Media", "CharityDescription" => "Media and entertainment services", "LanguageID" => 1]
            ],
                $charityIds['agriculture'] ?? null => [
                ["CharityName" => "Сельское хозяйство", "CharityDescription" => "Сельскохозяйственные технологии и производство", "LanguageID" => 2],
                ["CharityName" => "Agriculture", "CharityDescription" => "Agricultural technologies and production", "LanguageID" => 1]
            ],
                $charityIds['automotive'] ?? null => [
                ["CharityName" => "Автомобили", "CharityDescription" => "Автомобили и производство транспорта", "LanguageID" => 2],
                ["CharityName" => "Automotive", "CharityDescription" => "Automobiles and transportation manufacturing", "LanguageID" => 1]
            ],
                $charityIds['entertainment'] ?? null => [
                ["CharityName" => "Развлечения", "CharityDescription" => "Индустрия развлечений и шоу-бизнес", "LanguageID" => 2],
                ["CharityName" => "Entertainment", "CharityDescription" => "Entertainment and show business Charity", "LanguageID" => 1]
            ],
                $charityIds['manufacturing'] ?? null => [
                ["CharityName" => "Производство", "CharityDescription" => "Производственные отрасли", "LanguageID" => 2],
                ["CharityName" => "Manufacturing", "CharityDescription" => "Manufacturing Charities", "LanguageID" => 1]
            ],
                $charityIds['hospitality'] ?? null => [
                ["CharityName" => "Гостеприимство", "CharityDescription" => "Отели, рестораны и другие услуги гостеприимства", "LanguageID" => 2],
                ["CharityName" => "Hospitality", "CharityDescription" => "Hotels, restaurants, and other hospitality services", "LanguageID" => 1]
            ],
                $charityIds['other'] ?? null => [
                ["CharityName" => "Другое", "CharityDescription" => "Прочие индустрии", "LanguageID" => 2],
                ["CharityName" => "Other", "CharityDescription" => "Other Charities", "LanguageID" => 1]
            ]
        ];

        $createdTranslations = [];

        // Создаём переводы для каждой должности, используя их ID
        foreach ($translationsData as $charityIds => $translations) {
            if (!$charityIds) {
                continue; // Пропускаем, если ID не найден
            }

            $charity = $this->charityRepository->findCharityById($charityIds);

            foreach ($translations as $translationData) {
                try {
                    $languageData = $this->languagesProxyService->validateLanguageID($translationData['LanguageID']);
                    $languageId = $languageData['LanguageID'];
                    $this->charitiesValidationService->ensureUniqueTranslation($charity, $languageId);

                    $translation = new CharityTranslations();
                    $translation->setCharityID($charity);
                    $translation->setLanguageID($languageId);
                    $translation->setCharityName($translationData['CharityName']);
                    $translation->setCharityDescription($translationData['CharityDescription']);

                    $this->translationRepository->saveCharityTranslations($translation, true);
                    $createdTranslations[] = $this->charitiesValidationService->formatCharityTranslationsData($translation);

                    $this->logger->info("Translation for Charity ID '{$charityIds}' and LanguageID '{$languageId}' created successfully.");
                } catch (\Exception $e) {
                    $this->logger->error("Failed to add translation for Charity ID '$charityIds': " . $e->getMessage());
                }
            }
        }

        return [
            'charities' => $createdCharities,
            'translations' => $createdTranslations,
            'message' => 'Charity and translations seeded successfully.'
        ];
    }
}
