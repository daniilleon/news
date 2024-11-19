<?php

namespace Module\Persons\EducationLevels\Service;

use Module\Common\Helpers\FieldUpdateHelper;
use Module\Common\Service\ImageService;
use Module\Persons\EducationLevels\Entity\EducationLevels;
use Module\Persons\EducationLevels\Entity\EducationLevelTranslations;
use Module\Persons\EducationLevels\Repository\EducationLevelsRepository;
use Module\Persons\EducationLevels\Repository\EducationLevelTranslationsRepository;
use Module\Common\Service\LanguagesProxyService;
use Psr\Log\LoggerInterface;

class EducationLevelsService
{
    private EducationLevelsRepository $educationLevelRepository;
    private EducationLevelTranslationsRepository $translationRepository;
    private LanguagesProxyService $languagesProxyService;
    private EducationLevelsValidationService $educationLevelsValidationService;
    private LoggerInterface $logger;
    private ImageService $imageService;
    private FieldUpdateHelper $helper;

    public function __construct(
        EducationLevelsRepository            $educationLevelRepository,
        EducationLevelTranslationsRepository $translationRepository,
        LanguagesProxyService $languagesProxyService,
        EducationLevelsValidationService     $educationLevelsValidationService,
        ImageService                           $imageService,
        FieldUpdateHelper                      $helper,
        LoggerInterface                        $logger
    ) {
        $this->educationLevelRepository = $educationLevelRepository;
        $this->translationRepository = $translationRepository;
        $this->languagesProxyService = $languagesProxyService;
        $this->educationLevelsValidationService = $educationLevelsValidationService;
        $this->imageService = $imageService;
        $this->helper = $helper;
        $this->logger = $logger;
    }


    /**
     * Получение всех EducationLevel типов образований.
     * @return array
     */
    public function getAllEducationLevels(): array
    {
        try {
            $this->logger->info("Executing getAllEducationLevels method.");
            $educationLevel = $this->educationLevelRepository->findAllEducationLevels();

            // Проверка, есть ли языки
            if (empty($educationLevel)) {
                $this->logger->info("No EducationLevels found in the database.");
                return [
                    'educationLevels' => [],
                    'message' => 'No EducationLevels found in the database.'
                ];
            }
            // Форматируем каждый тип образования и добавляем ключ для структурированного ответа
            return [
                'educationLevels' => array_map([$this->educationLevelsValidationService, 'formatEducationLevelData'], $educationLevel),
                'message' => 'EducationLevels retrieved successfully.'
            ];
        } catch (\InvalidArgumentException $e) {
            $this->logger->error("Validation error while fetching EducationLevels: " . $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error("Unable to fetch languages: " . $e->getMessage());
            throw new \RuntimeException("Unable to fetch EducationLevels at the moment.", 0, $e);
        }
    }

    /**
     * Получение типа образования по ID вместе с переводами.
     *
     * @param int $id
     * @return array|null
     */
    public function getEducationLevelById(int $id): ?array
    {
        $this->logger->info("Executing getEducationLevelById method for ID: $id");
        // Используем validateEducationLevelExists для получения типа образования или выброса исключения
        $educationLevel = $this->educationLevelsValidationService->validateEducationLevelExists($id);
        $translations = $this->translationRepository->findTranslationsByEducationLevel($educationLevel);
        // Форматируем данные типа образования и переводов
        return [
            'educationLevel' => $this->educationLevelsValidationService->formatEducationLevelData($educationLevel),
            'translations' => array_map([$this->educationLevelsValidationService, 'formatEducationLevelTranslationsData'], $translations),
            'message' => "EducationLevel with ID $id retrieved successfully."
        ];
    }

    /**
     * Создание нового типа образования.
     *
     * @param array $data
     * @return array
     */
    public function createEducationLevel(array $data): array
    {
        $this->logger->info("Executing createEducationLevel method.");
        try {
            // Преобразование кода в верхний регистр непосредственно перед присвоением
            if (isset($data['EducationLevelCode'])) {
                $data['EducationLevelCode'] = strtoupper($data['EducationLevelCode']);
                $this->logger->info("EducationLevelCode after strtoupper: " . $data['EducationLevelCode']);
            }
            // Валидация данных для типа образования
            $this->educationLevelsValidationService->validateEducationLevelCode($data);
            $this->educationLevelsValidationService->ensureUniqueEducationLevelCode($data['EducationLevelCode'] ?? null);

            // Создаем новый тип образования
            $educationLevel = new EducationLevels();
            $this->helper->validateAndFilterFields($educationLevel, $data);//проверяем список разрешенных полей
            $educationLevel->setEducationLevelCode($data['EducationLevelCode']);

            // Сохраняем тип образования в репозитории
            $this->educationLevelRepository->saveEducationLevels($educationLevel, true);
            $this->logger->info("EducationLevel '{$educationLevel->getEducationLevelCode()}' created successfully.");

            // Форматируем ответ
            return [
                'educationLevel' => $this->educationLevelsValidationService->formatEducationLevelData($educationLevel),
                'message' => 'EducationLevel added successfully.'
            ];
        } catch (\InvalidArgumentException $e) {
            // Логируем ошибку валидации, чтобы избежать дублирования в контроллере
            $this->logger->error("Validation failed for creating EducationLevel: " . $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            // Логируем общую ошибку и выбрасываем исключение
            $this->logger->error("An unexpected error occurred while creating EducationLevel: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Добавление перевода для существующего типа образования.
     *
     * @param int $educationLevelId
     * @param array $data
     * @return array
     * @throws \InvalidArgumentException Если не удалось найти тип образования или язык
     * @throws \Exception Если произошла непредвиденная ошибка
     */
    public function createEducationLevelTranslation(int $educationLevelId, array $data): array
    {
        $this->logger->info("Executing createEducationLevelTranslation method for EducationLevel ID: $educationLevelId.");
        try {
            // Проверяем существование типа образования
            $educationLevel = $this->educationLevelsValidationService->validateEducationLevelExists($educationLevelId);

            // Проверяем наличие выполняем валидацию
            $this->educationLevelsValidationService->validateEducationLevelTranslationData($data);
            // Проверяем обязательность поля EducationLevelName
            $this->educationLevelsValidationService->ensureUniqueEducationLevelName($data['EducationLevelName'] ?? null);
            // Проверка на наличие LanguageID и указать, что это обязательный параметр
            $languageData = $this->languagesProxyService->validateLanguageID($data['LanguageID']  ?? null);

            // Проверка уникальности перевода
            $this->educationLevelsValidationService->ensureUniqueTranslation($educationLevel, $languageData['LanguageID']);

            // Создание нового перевода
            $translation = new EducationLevelTranslations();
            $this->helper->validateAndFilterFields($translation, $data);//проверяем список разрешенных полей
            $translation->setEducationLevelID($educationLevel);
            $translation->setLanguageID($languageData['LanguageID']);

            // Применение дополнительных полей
            foreach ($data as $field => $value) {
                $setter = 'set' . ucfirst($field);
                if (method_exists($translation, $setter) && !in_array($field, ['EducationLevelID', 'LanguageID'])) {
                    $translation->$setter($value);
                } elseif (!in_array($field, ['EducationLevelID', 'LanguageID'])) {
                    throw new \InvalidArgumentException("Field '$field' does not exist on EducationLevelTranslation entity.");
                }
            }
            $this->helper->validateAndFilterFields($translation, $data);//проверяем список разрешенных полей
            // Сохранение перевода
            $this->translationRepository->saveEducationLevelTranslations($translation, true);
            $this->logger->info("Translation for EducationLevel ID $educationLevelId created successfully.");

            return [
                'educationLevel' => $this->educationLevelsValidationService->formatEducationLevelData($educationLevel),
                'translation' => $this->educationLevelsValidationService->formatEducationLevelTranslationsData($translation),
                'message' => 'EducationLevel translation added successfully.'
            ];

        } catch (\InvalidArgumentException $e) {
            // Логируем и передаем исключение с детализированным сообщением
            $this->logger->error("Validation failed for EducationLevel ID $educationLevelId: " . $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            // Обработка неожиданных ошибок
            $this->logger->error("An unexpected error occurred while creating translation for EducationLevel ID $educationLevelId: " . $e->getMessage());
            throw new \RuntimeException("Unable to add EducationLevel translation", 0, $e);
        }
    }

    //Обновление типа образования (Code статуса)
    public function updateEducationLevelCode(int $educationLevelId, array $data): array
    {
        $this->logger->info("Executing updateEducationLevelCode method for EducationLevel ID: $educationLevelId");

        try {
            // Получаем тип образования по ID и проверяем его существование
            $educationLevel = $this->educationLevelsValidationService->validateEducationLevelExists($educationLevelId);
            if (!$educationLevel) {
                $this->logger->warning("EducationLevel with ID $educationLevelId not found for updating.");
                throw new \InvalidArgumentException("EducationLevel with ID $educationLevelId not found.");
            }
            // Преобразование кода в верхний регистр непосредственно перед присвоением
            if (isset($data['EducationLevelCode'])) {
                $data['EducationLevelCode'] = strtoupper($data['EducationLevelCode']);
                $this->logger->info("EducationLevelCode after strtoupper: " . $data['EducationLevelCode']);
            }

            // Проверка наличия обязательных полей и их значений (в данных или в объекте)
            $requiredFields = ['EducationLevelCode'];
            foreach ($requiredFields as $field) {
                $value = $data[$field] ?? $educationLevel->{'get' . $field}();
                if (empty($value)) {
                    throw new \InvalidArgumentException("Field '$field' is required and cannot be empty.");
                }
            }

            // Используем FieldUpdateHelper для обновления поля EducationLevelCode с проверками
            FieldUpdateHelper::updateFieldIfPresent(
                $educationLevel,
                $data,
                'EducationLevelCode',
                function ($newCode) use ($educationLevelId) {
                    $this->educationLevelsValidationService->ensureUniqueEducationLevelCode($newCode, $educationLevelId);
                    $this->educationLevelsValidationService->validateEducationLevelCode(['EducationLevelCode' => $newCode]);
                }
            );

            $this->helper->validateAndFilterFields($educationLevel, $data);//проверяем список разрешенных полей
            $this->educationLevelRepository->saveEducationLevels($educationLevel, true);

            $this->logger->info("EducationLevel Code updated successfully for EducationLevel ID: $educationLevelId");

            return [
                'educationLevel' => $this->educationLevelsValidationService->formatEducationLevelData($educationLevel),
                'message' => 'EducationLevel Code updated successfully.'
            ];
        } catch (\InvalidArgumentException $e) {
            $this->logger->error("Validation failed for EducationLevel ID $educationLevelId: " . $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error("An unexpected error occurred while updating EducationLevel Code for ID $educationLevelId: " . $e->getMessage());
            throw new \RuntimeException("Unable to update EducationLevel Code", 0, $e);
        }
    }


    //Обновление переводов у типа образования
    public function updateEducationLevelTranslation(int $educationLevelId, int $translationId, array $data): array
    {
        $this->logger->info("Updating translation for EducationLevel ID: $educationLevelId and Translation ID: $translationId");

        try {
            $educationLevel = $this->educationLevelsValidationService->validateEducationLevelExists($educationLevelId);

            // Поиск перевода по ID
            $translation = $this->translationRepository->find($translationId);
            if (!$translation || $translation->getEducationLevelID()->getEducationLevelID() !== $educationLevelId) {
                $this->logger->error("Translation for EducationLevel ID $educationLevelId and Translation ID $translationId not found.");
                throw new \InvalidArgumentException("Translation with this ID does not exist for this EducationLevel.");
            }

            // Проверка, что LanguageID не был передан в запросе
            $this->languagesProxyService->checkImmutableLanguageID($data, $translation->getLanguageID());

            // Проверка наличия обязательных полей и их значений (в данных или в объекте)
            $requiredFields = ['EducationLevelName'];
            foreach ($requiredFields as $field) {
                $value = $data[$field] ?? $translation->{'get' . $field}();
                if (empty($value)) {
                    throw new \InvalidArgumentException("Field '$field' is required and cannot be empty.");
                }
            }
            // Валидация всех данных, переданных в $data
            $this->educationLevelsValidationService->validateEducationLevelTranslationData($data);

            // Обновление поля EducationLevelName с проверкой уникальности и наличия
            FieldUpdateHelper::updateFieldIfPresent(
                $translation,
                $data,
                'EducationLevelName',
                function ($newName) use ($translationId) {
                    $this->educationLevelsValidationService->ensureUniqueEducationLevelName($newName, $translationId);
                }
            );

            // Обновление полей перевода, игнорируя LanguageID
            foreach ($data as $field => $value) {
                $setter = 'set' . ucfirst($field);
                if (method_exists($translation, $setter) && !in_array($field, ['EducationLevelID'])) {
                    $translation->$setter($value);
                } elseif (!in_array($field, ['EducationLevelID'])) {
                    throw new \InvalidArgumentException("Field '$field' does not exist on EducationLevelTranslation entity.");
                }
            }
            $this->helper->validateAndFilterFields($translation, $data);//проверяем список разрешенных полей
            $this->translationRepository->saveEducationLevelTranslations($translation, true);
            $this->logger->info("Translation updated successfully for EducationLevel ID: $educationLevelId and Translation ID: $translationId");

            return [
                'educationLevel' => $this->educationLevelsValidationService->formatEducationLevelData($educationLevel),
                'translation' => $this->educationLevelsValidationService->formatEducationLevelTranslationsData($translation),
                'message' => 'EducationLevel translation updated successfully.'
            ];
        } catch (\InvalidArgumentException $e) {
            $this->logger->error("Validation failed for EducationLevel ID $educationLevelId and Translation ID $translationId: " . $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error("An unexpected error occurred while updating translation for EducationLevel ID $educationLevelId and Translation ID $translationId: " . $e->getMessage());
            throw new \RuntimeException("Unable to update EducationLevel translation", 0, $e);
        }
    }

    /**
     * Удаление перевода типа образования.
     *
     * @param int $educationLevelId
     * @param int $translationId
     * @return array
     */
    public function deleteEducationLevelTranslation(int $educationLevelId, int $translationId): array
    {
        $this->logger->info("Deleting translation with ID: $translationId for EducationLevel ID: $educationLevelId");

        try {
            // Проверка существования типа образования
            $this->educationLevelsValidationService->validateEducationLevelExists($educationLevelId);

            // Проверка существования перевода для данного типа образования
            $translation = $this->translationRepository->find($translationId);
            if (!$translation || $translation->getEducationLevelID()->getEducationLevelID() !== $educationLevelId) {
                $this->logger->error("Translation with ID $translationId does not exist for EducationLevel ID $educationLevelId.");
                throw new \InvalidArgumentException("Translation with this ID does not exist for this EducationLevel.");
            }

            // Удаление перевода
            $this->translationRepository->deleteEducationLevelTranslations($translation, true);
            $this->logger->info("Translation with ID $translationId successfully deleted for EducationLevel ID $educationLevelId.");

            return [
                'message' => "Translation with ID $translationId successfully deleted for EducationLevel ID $educationLevelId.",
            ];
        } catch (\InvalidArgumentException $e) {
            $this->logger->warning($e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error("An unexpected error occurred while deleting translation with ID $translationId for EducationLevel ID $educationLevelId: " . $e->getMessage());
            throw new \RuntimeException("Unable to delete EducationLevel translation", 0, $e);
        }
    }


    /**
     * Удаление типа образования.
     *
     * @param int $educationLevelId
     * @return array
     */
    public function deleteEducationLevel(int $educationLevelId): array
    {
        try {
            $this->logger->info("Executing deleteEducationLevel method for ID: $educationLevelId");

            $educationLevel = $this->educationLevelsValidationService->validateEducationLevelExists($educationLevelId);


            // Удаляем переводы типа образования
            $translations = $this->translationRepository->findTranslationsByEducationLevel($educationLevel);
            foreach ($translations as $translation) {
                $this->translationRepository->deleteEducationLevelTranslations($translation, true);
            }

            // Удаляем сам тип образования
            $this->educationLevelRepository->deleteEducationLevels($educationLevel, true);
            $this->logger->info("EducationLevel with ID $educationLevelId and its translations successfully deleted.");

            return [
                'message' => "EducationLevel with ID $educationLevelId and its translations successfully deleted.",
            ];
        } catch (\InvalidArgumentException $e) {
            $this->logger->warning($e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error("An unexpected error occurred while deleting EducationLevel with ID $educationLevelId: " . $e->getMessage());
            throw $e;
        }
    }

    public function seedEducationLevelAndTranslations(): array
    {
        $this->logger->info("Executing seedEducationLevelAndTranslations method.");

        // Данные для предустановленных типов образований
        $educationLevelData = [
            ["EducationLevelCode" => "HIGH_SCHOOL"],
            ["EducationLevelCode" => "SECONDARY_EDUCATION"],
            ["EducationLevelCode" => "VOCATIONAL_TRAINING"],
            ["EducationLevelCode" => "ASSOCIATE_DEGREE"],
            ["EducationLevelCode" => "BACHELOR_DEGREE"],
            ["EducationLevelCode" => "MASTER_DEGREE"],
            ["EducationLevelCode" => "DOCTORATE_DEGREE"],
            ["EducationLevelCode" => "CERTIFICATE_PROGRAM"],
            ["EducationLevelCode" => "DIPLOMA_PROGRAM"],
            ["EducationLevelCode" => "POSTGRADUATE_STUDIES"],
            ["EducationLevelCode" => "NO_FORMAL_EDUCATION"],
            ["EducationLevelCode" => "SELF_TAUGHT"],
            ["EducationLevelCode" => "OTHER"]
        ];

        $createdEducationLevel = [];
        $educationLevelIds = [];

        // Создаём Статусы семейного положения и сохраняем их ID
        foreach ($educationLevelData as $maritalData) {
            try {
                $this->educationLevelsValidationService->validateEducationLevelCode($maritalData);
                $this->educationLevelsValidationService->ensureUniqueEducationLevelCode($maritalData['EducationLevelCode']);

                $educationLevel = new EducationLevels();
                $educationLevel->setEducationLevelCode($maritalData['EducationLevelCode']);
                $this->educationLevelRepository->saveEducationLevels($educationLevel, true);

                $createdEducationLevel[] = $this->educationLevelsValidationService->formatEducationLevelData($educationLevel);
                $educationLevelIds[$maritalData['EducationLevelCode']] = $educationLevel->getEducationLevelID(); // Сохраняем ID типа образования

                $this->logger->info("Education Level '{$maritalData['EducationLevelCode']}' created successfully.");
            } catch (\InvalidArgumentException $e) {
                $this->logger->warning("Education Level '{$maritalData['EducationLevelCode']}' already exists or is invalid. Skipping.");
            }
        }

        // Данные для переводов типов образований, привязанные к EducationLevelID
        $translationsData = [
                $educationLevelIds['HIGH_SCHOOL'] ?? null => [
                ["EducationLevelName" => "Старшая школа", "LanguageID" => 2],
                ["EducationLevelName" => "High School", "LanguageID" => 1],
            ],
                $educationLevelIds['SECONDARY_EDUCATION'] ?? null => [
                ["EducationLevelName" => "Общее среднее образование", "LanguageID" => 2],
                ["EducationLevelName" => "Secondary Education", "LanguageID" => 1],
            ],
                $educationLevelIds['VOCATIONAL_TRAINING'] ?? null => [
                ["EducationLevelName" => "Профессиональное обучение", "LanguageID" => 2],
                ["EducationLevelName" => "Vocational Training", "LanguageID" => 1],
            ],
                $educationLevelIds['ASSOCIATE_DEGREE'] ?? null => [
                ["EducationLevelName" => "Асоциированный уровень", "LanguageID" => 2],
                ["EducationLevelName" => "Associate Degree", "LanguageID" => 1],
            ],
                $educationLevelIds['BACHELOR_DEGREE'] ?? null => [
                ["EducationLevelName" => "Бакалавр", "LanguageID" => 2],
                ["EducationLevelName" => "Bachelor's Degree", "LanguageID" => 1],
            ],
                $educationLevelIds['MASTER_DEGREE'] ?? null => [
                ["EducationLevelName" => "Магистр", "LanguageID" => 2],
                ["EducationLevelName" => "Master's Degree", "LanguageID" => 1],
            ],
                $educationLevelIds['DOCTORATE_DEGREE'] ?? null => [
                ["EducationLevelName" => "Доктор наук", "LanguageID" => 2],
                ["EducationLevelName" => "Doctorate Degree", "LanguageID" => 1],
            ],
                $educationLevelIds['CERTIFICATE_PROGRAM'] ?? null => [
                ["EducationLevelName" => "Сертификационная программа", "LanguageID" => 2],
                ["EducationLevelName" => "Certificate Program", "LanguageID" => 1],
            ],
                $educationLevelIds['DIPLOMA_PROGRAM'] ?? null => [
                ["EducationLevelName" => "Дипломная программа", "LanguageID" => 2],
                ["EducationLevelName" => "Diploma Program", "LanguageID" => 1],
            ],
                $educationLevelIds['POSTGRADUATE_STUDIES'] ?? null => [
                ["EducationLevelName" => "Послевузовское образование", "LanguageID" => 2],
                ["EducationLevelName" => "Postgraduate Studies", "LanguageID" => 1],
            ],
                $educationLevelIds['NO_FORMAL_EDUCATION'] ?? null => [
                ["EducationLevelName" => "Без формального образования", "LanguageID" => 2],
                ["EducationLevelName" => "No Formal Education", "LanguageID" => 1],
            ],
                $educationLevelIds['SELF_TAUGHT'] ?? null => [
                ["EducationLevelName" => "Самообразование", "LanguageID" => 2],
                ["EducationLevelName" => "Self-taught", "LanguageID" => 1],
            ],
                $educationLevelIds['OTHER'] ?? null => [
                ["EducationLevelName" => "Другое", "LanguageID" => 2],
                ["EducationLevelName" => "Other", "LanguageID" => 1],
            ],
        ];

        $createdTranslations = [];

        // Создаём переводы для каждого типа образования, используя их ID
        foreach ($translationsData as $educationLevelId => $translations) {
            if (!$educationLevelId) {
                continue; // Пропускаем, если ID не найден
            }

            $educationLevel = $this->educationLevelRepository->findEducationLevelById($educationLevelId);

            foreach ($translations as $translationData) {
                try {
                    $languageData = $this->languagesProxyService->validateLanguageID($translationData['LanguageID']);
                    $languageId = $languageData['LanguageID'];
                    $this->educationLevelsValidationService->ensureUniqueTranslation($educationLevel, $languageId);

                    $translation = new EducationLevelTranslations();
                    $translation->setEducationLevelID($educationLevel);
                    $translation->setLanguageID($languageId);
                    $translation->setEducationLevelName($translationData['EducationLevelName']);

                    $this->translationRepository->saveEducationLevelTranslations($translation, true);
                    $createdTranslations[] = $this->educationLevelsValidationService->formatEducationLevelTranslationsData($translation);

                    $this->logger->info("Translation for EducationLevel ID '{$educationLevelId}' and LanguageID '{$languageId}' created successfully.");
                } catch (\Exception $e) {
                    $this->logger->error("Failed to add translation for EducationLevel ID '$educationLevelId': " . $e->getMessage());
                }
            }
        }

        return [
            'educationLevel' => $createdEducationLevel,
            'translations' => $createdTranslations,
            'message' => 'EducationLevel and translations seeded successfully.'
        ];
    }

}
