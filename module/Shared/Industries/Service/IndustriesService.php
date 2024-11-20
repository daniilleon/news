<?php

namespace Module\Shared\Industries\Service;

use Module\Shared\Industries\Entity\Industries;
use Module\Shared\Industries\Entity\IndustryTranslations;
use Module\Shared\Industries\Repository\IndustriesRepository;
use Module\Shared\Industries\Repository\IndustryTranslationsRepository;
use Module\Common\Helpers\FieldUpdateHelper;
use Module\Common\Service\ImageService;
use Module\Common\Service\LanguagesProxyService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class IndustriesService
{
    private IndustriesRepository $industryRepository;
    private IndustryTranslationsRepository $translationRepository;
    private LanguagesProxyService $languagesProxyService;
    private IndustriesValidationService $industriesValidationService;
    private LoggerInterface $logger;
    private ImageService $imageService;
    private FieldUpdateHelper $helper;

    public function __construct(
        IndustriesRepository $industryRepository,
        IndustryTranslationsRepository $translationRepository,
        LanguagesProxyService $languagesProxyService,
        IndustriesValidationService $industriesValidationService,
        ImageService $imageService,
        FieldUpdateHelper $helper,
        LoggerInterface $logger
    ) {
        $this->industryRepository = $industryRepository;
        $this->translationRepository = $translationRepository;
        $this->languagesProxyService = $languagesProxyService;
        $this->industriesValidationService = $industriesValidationService;
        $this->imageService = $imageService;
        $this->helper = $helper;
        $this->logger = $logger;
    }


    /**
     * Получение всех категорий.
     * @return array
     */
    public function getAllIndustries(): array
    {
        try {
            $this->logger->info("Executing getAllIndustries method.");
            $industries = $this->industryRepository->findAllIndustries();

            // Проверка, есть ли языки
            if (empty($industries)) {
                $this->logger->info("No Industries found in the database.");
                return [
                    'industries' => [],
                    'message' => 'No Industries found in the database.'
                ];
            }
            // Форматируем каждую категорию и добавляем ключ для структурированного ответа
            return [
                'industries' => array_map([$this->industriesValidationService, 'formatIndustryData'], $industries),
                'message' => 'Industries retrieved successfully.'
            ];
        } catch (\InvalidArgumentException $e) {
            $this->logger->error("Validation error while fetching Industries: " . $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error("Unable to fetch languages: " . $e->getMessage());
            throw new \RuntimeException("Unable to fetch Industries at the moment.", 0, $e);
        }
    }

    /**
     * Получение категории по ID вместе с переводами.
     *
     * @param int $id
     * @return array|null
     */
    public function getIndustryById(int $id): ?array
    {
        $this->logger->info("Executing getIndustryById method for ID: $id");
        // Используем validateIndustryExists для получения категории или выброса исключения
        $industry = $this->industriesValidationService->validateIndustryExists($id);
        $translations = $this->translationRepository->findTranslationsByIndustry($industry);
        // Форматируем данные категории и переводов
        return [
            'industry' => $this->industriesValidationService->formatIndustryData($industry),
            'translations' => array_map([$this->industriesValidationService, 'formatIndustryTranslationsData'], $translations),
            'message' => "Industry with ID $id retrieved successfully."
        ];
    }

    /**
     * Создание новой категории.
     *
     * @param array $data
     * @return array
     */
    public function createIndustry(array $data): array
    {
        $this->logger->info("Executing createIndustry method.");
        try {
            // Валидация данных для категории
            $this->industriesValidationService->validateIndustryLink($data);
            $this->industriesValidationService->ensureUniqueIndustryLink($data['IndustryLink'] ?? null);

            // Создаем новую категорию
            $industry = new Industries();
            $this->helper->validateAndFilterFields($industry, $data);//проверяем список разрешенных полей
            $industry->setIndustryLink($data['IndustryLink']);

            // Сохраняем категорию в репозитории
            $this->industryRepository->saveIndustry($industry, true);
            $this->logger->info("Industry '{$industry->getIndustryLink()}' created successfully.");

            // Форматируем ответ
            return [
                'industry' => $this->industriesValidationService->formatIndustryData($industry),
                'message' => 'Industry added successfully.'
            ];
        } catch (\InvalidArgumentException $e) {
            // Логируем ошибку валидации, чтобы избежать дублирования в контроллере
            $this->logger->error("Validation failed for creating industry: " . $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            // Логируем общую ошибку и выбрасываем исключение
            $this->logger->error("An unexpected error occurred while creating industry: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Добавление перевода для существующей категории.
     *
     * @param int $industryId
     * @param array $data
     * @return array
     * @throws \InvalidArgumentException Если не удалось найти категорию или язык
     * @throws \Exception Если произошла непредвиденная ошибка
     */
    public function createIndustryTranslation(int $industryId, array $data): array
    {
        $this->logger->info("Executing createIndustryTranslation method for Industry ID: $industryId.");
        try {
            // Проверяем существование категории
            $industry = $this->industriesValidationService->validateIndustryExists($industryId);

            // Проверяем наличие выполняем валидацию
            $this->industriesValidationService->validateIndustryTranslationData($data);
            // Проверяем обязательность поля IndustryName
            $this->industriesValidationService->ensureUniqueIndustryName($data['IndustryName'] ?? null);
            // Проверка на наличие LanguageID и указать, что это обязательный параметр
            $languageData = $this->languagesProxyService->validateLanguageID($data['LanguageID']  ?? null);

            // Проверка уникальности перевода
            $this->industriesValidationService->ensureUniqueTranslation($industry, $languageData['LanguageID']);

            // Создание нового перевода
            $translation = new IndustryTranslations();
            $this->helper->validateAndFilterFields($translation, $data);//проверяем список разрешенных полей
            $translation->setIndustryID($industry);
            $translation->setLanguageID($languageData['LanguageID']);

            // Применение дополнительных полей
            foreach ($data as $field => $value) {
                $setter = 'set' . ucfirst($field);
                if (method_exists($translation, $setter) && !in_array($field, ['IndustryID', 'LanguageID'])) {
                    $translation->$setter($value);
                } elseif (!in_array($field, ['IndustryID', 'LanguageID'])) {
                    throw new \InvalidArgumentException("Field '$field' does not exist on IndustryTranslation entity.");
                }
            }
            $this->helper->validateAndFilterFields($translation, $data);//проверяем список разрешенных полей
            // Сохранение перевода
            $this->translationRepository->saveIndustryTranslations($translation, true);
            $this->logger->info("Translation for Industry ID $industryId created successfully.");

            return [
                'industry' => $this->industriesValidationService->formatIndustryData($industry),
                'translation' => $this->industriesValidationService->formatIndustryTranslationsData($translation),
                'message' => 'Industry translation added successfully.'
            ];

        } catch (\InvalidArgumentException $e) {
            // Логируем и передаем исключение с детализированным сообщением
            $this->logger->error("Validation failed for Industry ID $industryId: " . $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            // Обработка неожиданных ошибок
            $this->logger->error("An unexpected error occurred while creating translation for Industry ID $industryId: " . $e->getMessage());
            throw new \RuntimeException("Unable to add industry translation", 0, $e);
        }
    }

    public function updateIndustryLink(int $industryId, array $data): array
    {
        $this->logger->info("Updating industry link for industry ID: $industryId");

        try {
            // Получаем категорию по ID и проверяем ее существование
            $industry = $this->industriesValidationService->validateIndustryExists($industryId);
            if (!$industry) {
                $this->logger->warning("Industry with ID $industryId not found for updating.");
                throw new \InvalidArgumentException("Industry with ID $industryId not found.");
            }

            // Проверка наличия обязательных полей и их значений (в данных или в объекте)
            $requiredFields = ['IndustryLink'];
            foreach ($requiredFields as $field) {
                $value = $data[$field] ?? $industry->{'get' . $field}();
                if (empty($value)) {
                    throw new \InvalidArgumentException("Field '$field' is required and cannot be empty.");
                }
            }

            // Используем FieldUpdateHelper для обновления поля IndustryLink с проверками
            FieldUpdateHelper::updateFieldIfPresent(
                $industry,
                $data,
                'IndustryLink',
                function ($newLink) use ($industryId) {
                    $this->industriesValidationService->ensureUniqueIndustryLink($newLink, $industryId);
                    $this->industriesValidationService->validateIndustryLink(['IndustryLink' => $newLink]);
                }
            );

            $this->helper->validateAndFilterFields($industry, $data);//проверяем список разрешенных полей
            $this->industryRepository->saveIndustry($industry, true);

            $this->logger->info("Industry link updated successfully for Industry ID: $industryId");

            return [
                'industry' => $this->industriesValidationService->formatIndustryData($industry),
                'message' => 'Industry link updated successfully.'
            ];
        } catch (\InvalidArgumentException $e) {
            $this->logger->error("Validation failed for Industry ID $industryId: " . $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error("An unexpected error occurred while updating industry link for ID $industryId: " . $e->getMessage());
            throw new \RuntimeException("Unable to update industry link", 0, $e);
        }
    }

    //Обновление OgImage картинки у категории
    public function updateIndustryImage(int $industryId, ?UploadedFile $file): array
    {
        $this->logger->info("Executing updateIndustryImage method for Industry ID: $industryId.");

        try {
            // Проверяем, существует ли категория
            $industry = $this->industriesValidationService->validateIndustryExists($industryId);
            $oldImagePath = $industry->getOgImage();
            // Загружаем новое изображение и получаем путь
            $newImagePath = $this->imageService->uploadOgImage($file, $industryId, 'industries', $oldImagePath);
            // Устанавливаем новый путь для изображения
            $industry->setOgImage($newImagePath);

            // Сохраняем изменения
            $this->industryRepository->saveIndustry($industry, true);
            $this->logger->info("Image for Industry ID $industryId updated successfully.");

            // Возвращаем успешный ответ с новыми данными
            return [
                'industry' => $this->industriesValidationService->formatIndustryData($industry),
                'message' => 'Industry image updated successfully.'
            ];
        } catch (\InvalidArgumentException $e) {
            // Логируем ошибки валидации и выбрасываем исключение
            $this->logger->error("Validation failed for updating industry image: " . $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            // Логируем общую ошибку
            $this->logger->error("An unexpected error occurred while updating industry image: " . $e->getMessage());
            throw new \RuntimeException("Unable to update industry image at this time.", 0, $e);
        }
    }


    public function updateIndustryTranslation(int $industryId, int $translationId, array $data): array
    {
        $this->logger->info("Updating translation for Industry ID: $industryId and Translation ID: $translationId");

        try {
            $industry = $this->industriesValidationService->validateIndustryExists($industryId);

            // Поиск перевода по ID
            $translation = $this->translationRepository->find($translationId);
            if (!$translation || $translation->getIndustryID()->getIndustryID() !== $industryId) {
                $this->logger->error("Translation for Industry ID $industryId and Translation ID $translationId not found.");
                throw new \InvalidArgumentException("Translation with this ID does not exist for this industry.");
            }

            // Проверка, что LanguageID не был передан в запросе
            $this->languagesProxyService->checkImmutableLanguageID($data, $translation->getLanguageID());

            // Проверка наличия обязательных полей и их значений (в данных или в объекте)
            $requiredFields = ['IndustryName'];
            foreach ($requiredFields as $field) {
                $value = $data[$field] ?? $translation->{'get' . $field}();
                if (empty($value)) {
                    throw new \InvalidArgumentException("Field '$field' is required and cannot be empty.");
                }
            }
            // Валидация всех данных, переданных в $data
            $this->industriesValidationService->validateIndustryTranslationData($data);

            // Обновление поля IndustryName с проверкой уникальности и наличия
            FieldUpdateHelper::updateFieldIfPresent(
                $translation,
                $data,
                'IndustryName',
                function ($newName) use ($translationId) {
                    $this->industriesValidationService->ensureUniqueIndustryName($newName, $translationId);
                }
            );

            // Обновление полей перевода, игнорируя LanguageID
            foreach ($data as $field => $value) {
                $setter = 'set' . ucfirst($field);
                if (method_exists($translation, $setter) && !in_array($field, ['IndustryID'])) {
                    $translation->$setter($value);
                } elseif (!in_array($field, ['IndustryID'])) {
                    throw new \InvalidArgumentException("Field '$field' does not exist on IndustryTranslation entity.");
                }
            }
            $this->helper->validateAndFilterFields($translation, $data);//проверяем список разрешенных полей
            $this->translationRepository->saveIndustryTranslations($translation, true);
            $this->logger->info("Translation updated successfully for Industry ID: $industryId and Translation ID: $translationId");

            return [
                'industry' => $this->industriesValidationService->formatIndustryData($industry),
                'translation' => $this->industriesValidationService->formatIndustryTranslationsData($translation),
                'message' => 'Industry translation updated successfully.'
            ];
        } catch (\InvalidArgumentException $e) {
            $this->logger->error("Validation failed for Industry ID $industryId and Translation ID $translationId: " . $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error("An unexpected error occurred while updating translation for industry ID $industryId and Translation ID $translationId: " . $e->getMessage());
            throw new \RuntimeException("Unable to update industry translation", 0, $e);
        }
    }

    /**
     * Удаление перевода категории.
     *
     * @param int $industryId
     * @param int $translationId
     * @return array
     */
    public function deleteIndustryTranslation(int $industryId, int $translationId): array
    {
        $this->logger->info("Deleting translation with ID: $translationId for Industry ID: $industryId");

        try {
            // Проверка существования категории
            $this->industriesValidationService->validateIndustryExists($industryId);

            // Проверка существования перевода для данной категории
            $translation = $this->translationRepository->find($translationId);
            if (!$translation || $translation->getIndustryID()->getIndustryID() !== $industryId) {
                $this->logger->error("Translation with ID $translationId does not exist for Industry ID $industryId.");
                throw new \InvalidArgumentException("Translation with this ID does not exist for this industry.");
            }

            // Удаление перевода
            $this->translationRepository->deleteIndustryTranslations($translation, true);
            $this->logger->info("Translation with ID $translationId successfully deleted for Industry ID $industryId.");

            return [
                'message' => "Translation with ID $translationId successfully deleted for Industry ID $industryId.",
            ];
        } catch (\InvalidArgumentException $e) {
            $this->logger->warning($e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error("An unexpected error occurred while deleting translation with ID $translationId for Industry ID $industryId: " . $e->getMessage());
            throw new \RuntimeException("Unable to delete industry translation", 0, $e);
        }
    }


    /**
     * Удаление категории.
     *
     * @param int $industryId
     * @return array
     */
    public function deleteIndustry(int $industryId): array
    {
        try {
            $this->logger->info("Executing deleteIndustry method for ID: $industryId");

            $industry = $this->industriesValidationService->validateIndustryExists($industryId);


            // Удаляем переводы категории
            $translations = $this->translationRepository->findTranslationsByIndustry($industry);
            foreach ($translations as $translation) {
                $this->translationRepository->deleteIndustryTranslations($translation, true);
            }

            // Удаляем саму категорию
            $this->industryRepository->deleteIndustry($industry, true);
            $this->logger->info("Industry with ID $industryId and its translations successfully deleted.");

            return [
                'message' => "Industry with ID $industryId and its translations successfully deleted.",
            ];
        } catch (\InvalidArgumentException $e) {
            $this->logger->warning($e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error("An unexpected error occurred while deleting industry with ID $industryId: " . $e->getMessage());
            throw $e;
        }
    }


    /*/
    Методы для демо данных
    /*/
    public function seedIndustriesAndTranslations(): array
    {
        $this->logger->info("Executing seedJobTitlesAndTranslations method.");

        // Данные для предустановленных типов индустрий
        $industriesData = [
            ["IndustryLink" => "technology"],
            ["IndustryLink" => "finance"],
            ["IndustryLink" => "healthcare"],
            ["IndustryLink" => "real_estate"],
            ["IndustryLink" => "energy"],
            ["IndustryLink" => "education"],
            ["IndustryLink" => "transportation"],
            ["IndustryLink" => "retail"],
            ["IndustryLink" => "media"],
            ["IndustryLink" => "agriculture"],
            ["IndustryLink" => "automotive"],
            ["IndustryLink" => "entertainment"],
            ["IndustryLink" => "manufacturing"],
            ["IndustryLink" => "hospitality"],
            ["IndustryLink" => "other"]
        ];


        $createdIndustries = [];
        $industryIds = [];

        // Создаём должности и сохраняем их ID
        foreach ($industriesData as $industryData) {
            try {
                $this->industriesValidationService->validateIndustryLink($industryData);
                $this->industriesValidationService->ensureUniqueIndustryLink($industryData['IndustryLink']);

                $industry = new Industries();
                $industry->setIndustryLink($industryData['IndustryLink']);
                $this->industryRepository->saveIndustry($industry, true);

                $createdIndustries[] = $this->industriesValidationService->formatIndustryData($industry);
                $industryIds[$industryData['IndustryLink']] = $industry->getIndustryID(); // Сохраняем ID должности

                $this->logger->info("Industry Link '{$industryData['IndustryLink']}' created successfully.");
            } catch (\InvalidArgumentException $e) {
                $this->logger->warning("Industry Link '{$industryData['IndustryLink']}' already exists or is invalid. Skipping.");
            }
        }

        // Данные для переводов Индустрии, привязанные к IndustryID
        $translationsData = [
                $industryIds['technology'] ?? null => [
                ["IndustryName" => "Технологии", "IndustryDescription" => "Все отрасли, связанные с информационными технологиями", "LanguageID" => 2],
                ["IndustryName" => "Technology", "IndustryDescription" => "All technology-related industries", "LanguageID" => 1]
            ],
                $industryIds['finance'] ?? null => [
                ["IndustryName" => "Финансы", "IndustryDescription" => "Финансовые услуги и рынки", "LanguageID" => 2],
                ["IndustryName" => "Finance", "IndustryDescription" => "Financial services and markets", "LanguageID" => 1]
            ],
                $industryIds['healthcare'] ?? null => [
                ["IndustryName" => "Здравоохранение", "IndustryDescription" => "Услуги и технологии в здравоохранении", "LanguageID" => 2],
                ["IndustryName" => "Healthcare", "IndustryDescription" => "Healthcare services and technologies", "LanguageID" => 1]
            ],
                $industryIds['real_estate'] ?? null => [
                ["IndustryName" => "Недвижимость", "IndustryDescription" => "Строительство и продажа недвижимости", "LanguageID" => 2],
                ["IndustryName" => "Real Estate", "IndustryDescription" => "Construction and real estate sales", "LanguageID" => 1]
            ],
                $industryIds['energy'] ?? null => [
                ["IndustryName" => "Энергетика", "IndustryDescription" => "Отрасли, связанные с энергией и ресурсами", "LanguageID" => 2],
                ["IndustryName" => "Energy", "IndustryDescription" => "Energy and resource-related industries", "LanguageID" => 1]
            ],
                $industryIds['education'] ?? null => [
                ["IndustryName" => "Образование", "IndustryDescription" => "Услуги и технологии в образовании", "LanguageID" => 2],
                ["IndustryName" => "Education", "IndustryDescription" => "Education services and technologies", "LanguageID" => 1]
            ],
                $industryIds['transportation'] ?? null => [
                ["IndustryName" => "Транспорт", "IndustryDescription" => "Перевозки и логистика", "LanguageID" => 2],
                ["IndustryName" => "Transportation", "IndustryDescription" => "Transportation and logistics", "LanguageID" => 1]
            ],
                $industryIds['retail'] ?? null => [
                ["IndustryName" => "Розничная торговля", "IndustryDescription" => "Продажа товаров и услуг", "LanguageID" => 2],
                ["IndustryName" => "Retail", "IndustryDescription" => "Goods and services retail", "LanguageID" => 1]
            ],
                $industryIds['media'] ?? null => [
                ["IndustryName" => "Медиа", "IndustryDescription" => "Медиа и развлекательные услуги", "LanguageID" => 2],
                ["IndustryName" => "Media", "IndustryDescription" => "Media and entertainment services", "LanguageID" => 1]
            ],
                $industryIds['agriculture'] ?? null => [
                ["IndustryName" => "Сельское хозяйство", "IndustryDescription" => "Сельскохозяйственные технологии и производство", "LanguageID" => 2],
                ["IndustryName" => "Agriculture", "IndustryDescription" => "Agricultural technologies and production", "LanguageID" => 1]
            ],
                $industryIds['automotive'] ?? null => [
                ["IndustryName" => "Автомобили", "IndustryDescription" => "Автомобили и производство транспорта", "LanguageID" => 2],
                ["IndustryName" => "Automotive", "IndustryDescription" => "Automobiles and transportation manufacturing", "LanguageID" => 1]
            ],
                $industryIds['entertainment'] ?? null => [
                ["IndustryName" => "Развлечения", "IndustryDescription" => "Индустрия развлечений и шоу-бизнес", "LanguageID" => 2],
                ["IndustryName" => "Entertainment", "IndustryDescription" => "Entertainment and show business industry", "LanguageID" => 1]
            ],
                $industryIds['manufacturing'] ?? null => [
                ["IndustryName" => "Производство", "IndustryDescription" => "Производственные отрасли", "LanguageID" => 2],
                ["IndustryName" => "Manufacturing", "IndustryDescription" => "Manufacturing industries", "LanguageID" => 1]
            ],
                $industryIds['hospitality'] ?? null => [
                ["IndustryName" => "Гостеприимство", "IndustryDescription" => "Отели, рестораны и другие услуги гостеприимства", "LanguageID" => 2],
                ["IndustryName" => "Hospitality", "IndustryDescription" => "Hotels, restaurants, and other hospitality services", "LanguageID" => 1]
            ],
                $industryIds['other'] ?? null => [
                ["IndustryName" => "Другое", "IndustryDescription" => "Прочие индустрии", "LanguageID" => 2],
                ["IndustryName" => "Other", "IndustryDescription" => "Other industries", "LanguageID" => 1]
            ]
        ];

        $createdTranslations = [];

        // Создаём переводы для каждой должности, используя их ID
        foreach ($translationsData as $industryIds => $translations) {
            if (!$industryIds) {
                continue; // Пропускаем, если ID не найден
            }

            $industry = $this->industryRepository->findIndustryById($industryIds);

            foreach ($translations as $translationData) {
                try {
                    $languageData = $this->languagesProxyService->validateLanguageID($translationData['LanguageID']);
                    $languageId = $languageData['LanguageID'];
                    $this->industriesValidationService->ensureUniqueTranslation($industry, $languageId);

                    $translation = new IndustryTranslations();
                    $translation->setIndustryID($industry);
                    $translation->setLanguageID($languageId);
                    $translation->setIndustryName($translationData['IndustryName']);
                    $translation->setIndustryDescription($translationData['IndustryDescription']);

                    $this->translationRepository->saveIndustryTranslations($translation, true);
                    $createdTranslations[] = $this->industriesValidationService->formatIndustryTranslationsData($translation);

                    $this->logger->info("Translation for Industry ID '{$industryIds}' and LanguageID '{$languageId}' created successfully.");
                } catch (\Exception $e) {
                    $this->logger->error("Failed to add translation for Industry ID '$industryIds': " . $e->getMessage());
                }
            }
        }

        return [
            'industries' => $createdIndustries,
            'translations' => $createdTranslations,
            'message' => 'Industry and translations seeded successfully.'
        ];
    }
}
