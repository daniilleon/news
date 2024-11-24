<?php

namespace Module\Shared\MissionsStatements\Service;


use Module\Shared\MissionsStatements\Entity\MissionsStatements;
use Module\Shared\MissionsStatements\Entity\MissionStatementTranslations;
use Module\Shared\MissionsStatements\Repository\MissionsStatementsRepository;
use Module\Shared\MissionsStatements\Repository\MissionStatementTranslationsRepository;
use Module\Common\Helpers\FieldUpdateHelper;
use Module\Common\Service\ImageService;
use Module\Common\Proxy\Core\LanguagesProxyService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class MissionsStatementsService
{
    private MissionsStatementsRepository $missionStatementRepository;
    private MissionStatementTranslationsRepository $translationRepository;
    private LanguagesProxyService $languagesProxyService;
    private MissionsStatementsValidationService $missionStatementValidationService;
    private LoggerInterface $logger;
    private ImageService $imageService;
    private FieldUpdateHelper $helper;

    public function __construct(
        MissionsStatementsRepository $missionStatementRepository,
        MissionStatementTranslationsRepository $translationRepository,
        LanguagesProxyService $languagesProxyService,
        MissionsStatementsValidationService $missionStatementValidationService,
        ImageService $imageService,
        FieldUpdateHelper $helper,
        LoggerInterface $logger
    ) {
        $this->missionStatementRepository = $missionStatementRepository;
        $this->translationRepository = $translationRepository;
        $this->languagesProxyService = $languagesProxyService;
        $this->missionStatementValidationService = $missionStatementValidationService;
        $this->imageService = $imageService;
        $this->helper = $helper;
        $this->logger = $logger;
    }


    /**
     * Получение всех категорий.
     * @return array
     */
    public function getAllMissionStatement(): array
    {
        try {
            $this->logger->info("Executing getAllMissionsStatements method.");
            $missionStatement = $this->missionStatementRepository->findAllMissionsStatements();

            // Проверка, есть ли языки
            if (empty($missionStatement)) {
                $this->logger->info("No missionStatement found in the database.");
                return [
                    'missionStatement' => [],
                    'message' => 'No missionStatement found in the database.'
                ];
            }
            // Форматируем каждую категорию и добавляем ключ для структурированного ответа
            return [
                'missionStatement' => array_map([$this->missionStatementValidationService, 'formatMissionStatementData'], $missionStatement),
                'message' => 'missionStatement retrieved successfully.'
            ];
        } catch (\InvalidArgumentException $e) {
            $this->logger->error("Validation error while fetching MissionsStatements: " . $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error("Unable to fetch languages: " . $e->getMessage());
            throw new \RuntimeException("Unable to fetch MissionsStatements at the moment.", 0, $e);
        }
    }

    /**
     * Получение категории по ID вместе с переводами.
     *
     * @param int $id
     * @return array|null
     */
    public function getMissionStatementById(int $id): ?array
    {
        $this->logger->info("Executing getMissionStatementById method for ID: $id");
        // Используем validateMissionStatementExists для получения категории или выброса исключения
        $missionStatement = $this->missionStatementValidationService->validateMissionStatementExists($id);
        $translations = $this->translationRepository->findTranslationsByMissionStatement($missionStatement);
        // Форматируем данные категории и переводов
        return [
            'missionStatement' => $this->missionStatementValidationService->formatMissionStatementData($missionStatement),
            'translations' => array_map([$this->missionStatementValidationService, 'formatMissionStatementTranslationsData'], $translations),
            'message' => "MissionStatement with ID $id retrieved successfully."
        ];
    }

    /**
     * Создание новой категории.
     *
     * @param array $data
     * @return array
     */
    public function createMissionStatement(array $data): array
    {
        $this->logger->info("Executing createMissionStatement method.");
        try {
            // Валидация данных для категории
            $this->missionStatementValidationService->validateMissionStatementCode($data);
            $this->missionStatementValidationService->ensureUniqueMissionStatementCode($data['MissionStatementCode'] ?? null);

            // Создаем новую категорию
            $missionStatement = new MissionsStatements();
            $this->helper->validateAndFilterFields($missionStatement, $data);//проверяем список разрешенных полей
            $missionStatement->setMissionStatementCode($data['MissionStatementCode']);

            // Сохраняем категорию в репозитории
            $this->missionStatementRepository->saveMissionStatement($missionStatement, true);
            $this->logger->info("MissionStatement '{$missionStatement->getMissionStatementCode()}' created successfully.");

            // Форматируем ответ
            return [
                'MissionStatement' => $this->missionStatementValidationService->formatMissionStatementData($missionStatement),
                'message' => 'MissionStatement added successfully.'
            ];
        } catch (\InvalidArgumentException $e) {
            // Логируем ошибку валидации, чтобы избежать дублирования в контроллере
            $this->logger->error("Validation failed for creating MissionStatement: " . $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            // Логируем общую ошибку и выбрасываем исключение
            $this->logger->error("An unexpected error occurred while creating MissionStatement: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Добавление перевода для существующей категории.
     *
     * @param int $missionStatementId
     * @param array $data
     * @return array
     * @throws \InvalidArgumentException Если не удалось найти категорию или язык
     * @throws \Exception Если произошла непредвиденная ошибка
     */
    public function createMissionStatementTranslation(int $missionStatementId, array $data): array
    {
        $this->logger->info("Executing createMissionStatementTranslation method for MissionStatement ID: $missionStatementId.");
        try {
            // Проверяем существование категории
            $missionStatement = $this->missionStatementValidationService->validateMissionStatementExists($missionStatementId);

            // Проверяем наличие выполняем валидацию
            $this->missionStatementValidationService->validateMissionStatementTranslationData($data);
            // Проверяем обязательность поля MissionStatementName
            $this->missionStatementValidationService->ensureUniqueMissionStatementName($data['MissionStatementName'] ?? null);
            // Проверка на наличие LanguageID и указать, что это обязательный параметр
            $languageData = $this->languagesProxyService->validateLanguageID($data['LanguageID']  ?? null);

            // Проверка уникальности перевода
            $this->missionStatementValidationService->ensureUniqueTranslation($missionStatement, $languageData['LanguageID']);

            // Создание нового перевода
            $translation = new MissionStatementTranslations();
            $this->helper->validateAndFilterFields($translation, $data);//проверяем список разрешенных полей
            $translation->setMissionStatementID($missionStatement);
            $translation->setLanguageID($languageData['LanguageID']);

            // Применение дополнительных полей
            foreach ($data as $field => $value) {
                $setter = 'set' . ucfirst($field);
                if (method_exists($translation, $setter) && !in_array($field, ['MissionStatementID', 'LanguageID'])) {
                    $translation->$setter($value);
                } elseif (!in_array($field, ['MissionStatementID', 'LanguageID'])) {
                    throw new \InvalidArgumentException("Field '$field' does not exist on MissionStatementTranslation entity.");
                }
            }
            $this->helper->validateAndFilterFields($translation, $data);//проверяем список разрешенных полей
            // Сохранение перевода
            $this->translationRepository->saveMissionStatementTranslations($translation, true);
            $this->logger->info("Translation for MissionStatement ID $missionStatementId created successfully.");

            return [
                'missionStatement' => $this->missionStatementValidationService->formatMissionStatementData($missionStatement),
                'translation' => $this->missionStatementValidationService->formatMissionStatementTranslationsData($translation),
                'message' => 'MissionStatement translation added successfully.'
            ];

        } catch (\InvalidArgumentException $e) {
            // Логируем и передаем исключение с детализированным сообщением
            $this->logger->error("Validation failed for MissionStatement ID $missionStatementId: " . $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            // Обработка неожиданных ошибок
            $this->logger->error("An unexpected error occurred while creating translation for MissionStatement ID $missionStatementId: " . $e->getMessage());
            throw new \RuntimeException("Unable to add MissionStatement translation", 0, $e);
        }
    }

    public function updateMissionStatementCode(int $missionStatementId, array $data): array
    {
        $this->logger->info("Updating MissionStatement Code for MissionStatement ID: $missionStatementId");

        try {
            // Получаем категорию по ID и проверяем ее существование
            $missionStatement = $this->missionStatementValidationService->validateMissionStatementExists($missionStatementId);
            if (!$missionStatement) {
                $this->logger->warning("MissionStatement with ID $missionStatementId not found for updating.");
                throw new \InvalidArgumentException("MissionStatement with ID $missionStatementId not found.");
            }

            // Проверка наличия обязательных полей и их значений (в данных или в объекте)
            $requiredFields = ['MissionStatementCode'];
            foreach ($requiredFields as $field) {
                $value = $data[$field] ?? $missionStatement->{'get' . $field}();
                if (empty($value)) {
                    throw new \InvalidArgumentException("Field '$field' is required and cannot be empty.");
                }
            }

            // Используем FieldUpdateHelper для обновления поля MissionStatementCode с проверками
            FieldUpdateHelper::updateFieldIfPresent(
                $missionStatement,
                $data,
                'MissionStatementCode',
                function ($newCode) use ($missionStatementId) {
                    $this->missionStatementValidationService->ensureUniqueMissionStatementCode($newCode, $missionStatementId);
                    $this->missionStatementValidationService->validateMissionStatementCode(['MissionStatementCode' => $newCode]);
                }
            );

            $this->helper->validateAndFilterFields($missionStatement, $data);//проверяем список разрешенных полей
            $this->missionStatementRepository->saveMissionStatement($missionStatement, true);

            $this->logger->info("MissionStatement Code updated successfully for MissionStatement ID: $missionStatementId");

            return [
                'MissionStatement' => $this->missionStatementValidationService->formatMissionStatementData($missionStatement),
                'message' => 'MissionStatement Code updated successfully.'
            ];
        } catch (\InvalidArgumentException $e) {
            $this->logger->error("Validation failed for MissionStatement ID $missionStatementId: " . $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error("An unexpected error occurred while updating MissionStatement Code for ID $missionStatementId: " . $e->getMessage());
            throw new \RuntimeException("Unable to update MissionStatement Code", 0, $e);
        }
    }

    //Обновление OgImage картинки у категории
//    public function updateMissionStatementImage(int $missionStatementId, ?UploadedFile $file): array
//    {
//        $this->logger->info("Executing updateMissionStatementImage method for MissionStatement ID: $missionStatementId.");
//
//        try {
//            // Проверяем, существует ли категория
//            $missionStatement = $this->missionStatementValidationService->validateMissionStatementExists($missionStatementId);
//            $oldImagePath = $missionStatement->getOgImage();
//            // Загружаем новое изображение и получаем путь
//            $newImagePath = $this->imageService->uploadOgImage($file, $missionStatementId, 'missionstatement', $oldImagePath);
//            // Устанавливаем новый путь для изображения
//            $missionStatement->setOgImage($newImagePath);
//
//            // Сохраняем изменения
//            $this->missionStatementRepository->saveMissionStatement($missionStatement, true);
//            $this->logger->info("Image for MissionStatement ID $missionStatementId updated successfully.");
//
//            // Возвращаем успешный ответ с новыми данными
//            return [
//                'MissionStatement' => $this->missionStatementValidationService->formatMissionStatementData($missionStatement),
//                'message' => 'MissionStatement image updated successfully.'
//            ];
//        } catch (\InvalidArgumentException $e) {
//            // Логируем ошибки валидации и выбрасываем исключение
//            $this->logger->error("Validation failed for updating MissionStatement image: " . $e->getMessage());
//            throw $e;
//        } catch (\Exception $e) {
//            // Логируем общую ошибку
//            $this->logger->error("An unexpected error occurred while updating MissionStatement image: " . $e->getMessage());
//            throw new \RuntimeException("Unable to update MissionStatement image at this time.", 0, $e);
//        }
//    }


    public function updateMissionStatementTranslation(int $missionStatementId, int $translationId, array $data): array
    {
        $this->logger->info("Updating translation for MissionStatement ID: $missionStatementId and Translation ID: $translationId");

        try {
            $missionStatement = $this->missionStatementValidationService->validateMissionStatementExists($missionStatementId);

            // Поиск перевода по ID
            $translation = $this->translationRepository->find($translationId);
            if (!$translation || $translation->getMissionStatementID()->getMissionStatementID() !== $missionStatementId) {
                $this->logger->error("Translation for MissionStatement ID $missionStatementId and Translation ID $translationId not found.");
                throw new \InvalidArgumentException("Translation with this ID does not exist for this MissionStatement.");
            }

            // Проверка, что LanguageID не был передан в запросе
            $this->languagesProxyService->checkImmutableLanguageID($data, $translation->getLanguageID());

            // Проверка наличия обязательных полей и их значений (в данных или в объекте)
            $requiredFields = ['MissionStatementName'];
            foreach ($requiredFields as $field) {
                $value = $data[$field] ?? $translation->{'get' . $field}();
                if (empty($value)) {
                    throw new \InvalidArgumentException("Field '$field' is required and cannot be empty.");
                }
            }
            // Валидация всех данных, переданных в $data
            $this->missionStatementValidationService->validateMissionStatementTranslationData($data);

            // Обновление поля MissionStatementName с проверкой уникальности и наличия
            FieldUpdateHelper::updateFieldIfPresent(
                $translation,
                $data,
                'MissionStatementName',
                function ($newName) use ($translationId) {
                    $this->missionStatementValidationService->ensureUniqueMissionStatementName($newName, $translationId);
                }
            );

            // Обновление полей перевода, игнорируя LanguageID
            foreach ($data as $field => $value) {
                $setter = 'set' . ucfirst($field);
                if (method_exists($translation, $setter) && !in_array($field, ['MissionStatementID'])) {
                    $translation->$setter($value);
                } elseif (!in_array($field, ['MissionStatementID'])) {
                    throw new \InvalidArgumentException("Field '$field' does not exist on MissionStatementTranslation entity.");
                }
            }
            $this->helper->validateAndFilterFields($translation, $data);//проверяем список разрешенных полей
            $this->translationRepository->saveMissionStatementTranslations($translation, true);
            $this->logger->info("Translation updated successfully for MissionStatement ID: $missionStatementId and Translation ID: $translationId");

            return [
                'MissionStatement' => $this->missionStatementValidationService->formatMissionStatementData($missionStatement),
                'translation' => $this->missionStatementValidationService->formatMissionStatementTranslationsData($translation),
                'message' => 'MissionStatement translation updated successfully.'
            ];
        } catch (\InvalidArgumentException $e) {
            $this->logger->error("Validation failed for MissionStatement ID $missionStatementId and Translation ID $translationId: " . $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error("An unexpected error occurred while updating translation for MissionStatement ID $missionStatementId and Translation ID $translationId: " . $e->getMessage());
            throw new \RuntimeException("Unable to update MissionStatement translation", 0, $e);
        }
    }

    /**
     * Удаление перевода категории.
     *
     * @param int $missionStatementId
     * @param int $translationId
     * @return array
     */
    public function deleteMissionStatementTranslation(int $missionStatementId, int $translationId): array
    {
        $this->logger->info("Deleting translation with ID: $translationId for MissionStatement ID: $missionStatementId");

        try {
            // Проверка существования категории
            $this->missionStatementValidationService->validateMissionStatementExists($missionStatementId);

            // Проверка существования перевода для данной категории
            $translation = $this->translationRepository->find($translationId);
            if (!$translation || $translation->getMissionStatementID()->getMissionStatementID() !== $missionStatementId) {
                $this->logger->error("Translation with ID $translationId does not exist for MissionStatement ID $missionStatementId.");
                throw new \InvalidArgumentException("Translation with this ID does not exist for this MissionStatement.");
            }

            // Удаление перевода
            $this->translationRepository->deleteMissionStatementTranslations($translation, true);
            $this->logger->info("Translation with ID $translationId successfully deleted for MissionStatement ID $missionStatementId.");

            return [
                'message' => "Translation with ID $translationId successfully deleted for MissionStatement ID $missionStatementId.",
            ];
        } catch (\InvalidArgumentException $e) {
            $this->logger->warning($e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error("An unexpected error occurred while deleting translation with ID $translationId for MissionStatement ID $missionStatementId: " . $e->getMessage());
            throw new \RuntimeException("Unable to delete MissionStatement translation", 0, $e);
        }
    }


    /**
     * Удаление категории.
     *
     * @param int $missionStatementId
     * @return array
     */
    public function deleteMissionStatement(int $missionStatementId): array
    {
        try {
            $this->logger->info("Executing deleteMissionStatement method for ID: $missionStatementId");

            $missionStatement = $this->missionStatementValidationService->validateMissionStatementExists($missionStatementId);


            // Удаляем переводы категории
            $translations = $this->translationRepository->findTranslationsByMissionStatement($missionStatement);
            foreach ($translations as $translation) {
                $this->translationRepository->deleteMissionStatementTranslations($translation, true);
            }

            // Удаляем саму категорию
            $this->missionStatementRepository->deleteMissionStatement($missionStatement, true);
            $this->logger->info("MissionStatement with ID $missionStatementId and its translations successfully deleted.");

            return [
                'message' => "MissionStatement with ID $missionStatementId and its translations successfully deleted.",
            ];
        } catch (\InvalidArgumentException $e) {
            $this->logger->warning($e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error("An unexpected error occurred while deleting MissionStatement with ID $missionStatementId: " . $e->getMessage());
            throw $e;
        }
    }


    /*/
    Методы для демо данных
    /*/
    public function seedMissionStatementAndTranslations(): array
    {
        $this->logger->info("Executing seedJobTitlesAndTranslations method.");

        // Данные для предустановленных типов индустрий
        $missionStatementsData = [
            ["MissionStatementCode" => "helping_people"],
            ["MissionStatementCode" => "environmental_protection"],
            ["MissionStatementCode" => "animal_welfare"],
            ["MissionStatementCode" => "education_support"],
            ["MissionStatementCode" => "healthcare_initiatives"],
            ["MissionStatementCode" => "poverty_reduction"],
            ["MissionStatementCode" => "disaster_relief"]
        ];


        $createdMissionStatement = [];
        $missionStatementIds = [];

        // Создаём должности и сохраняем их ID
        foreach ($missionStatementsData as $missionStatementData) {
            try {
                $this->missionStatementValidationService->validateMissionStatementCode($missionStatementData);
                $this->missionStatementValidationService->ensureUniqueMissionStatementCode($missionStatementData['MissionStatementCode']);

                $missionStatement = new MissionsStatements();
                $missionStatement->setMissionStatementCode($missionStatementData['MissionStatementCode']);
                $this->missionStatementRepository->saveMissionStatement($missionStatement, true);

                $createdMissionStatement[] = $this->missionStatementValidationService->formatMissionStatementData($missionStatement);
                $missionStatementIds[$missionStatementData['MissionStatementCode']] = $missionStatement->getMissionStatementID(); // Сохраняем ID должности

                $this->logger->info("MissionStatement Code '{$missionStatementData['MissionStatementCode']}' created successfully.");
            } catch (\InvalidArgumentException $e) {
                $this->logger->warning("MissionStatement Code '{$missionStatementData['MissionStatementCode']}' already exists or is invalid. Skipping.");
            }
        }

        // Данные для переводов Индустрии, привязанные к MissionStatementID
        $translationsData = [
                $missionStatementIds['helping_people'] ?? null => [
                ["MissionStatementName" => "Помощь людям", "MissionStatementDescription" => "Содействие людям, находящимся в сложных жизненных ситуациях", "LanguageID" => 2],
                ["MissionStatementName" => "Helping People", "MissionStatementDescription" => "Assisting people in difficult life situations", "LanguageID" => 1]
            ],
                $missionStatementIds['environmental_protection'] ?? null => [
                ["MissionStatementName" => "Защита окружающей среды", "MissionStatementDescription" => "Деятельность, направленная на сохранение природы", "LanguageID" => 2],
                ["MissionStatementName" => "Environmental Protection", "MissionStatementDescription" => "Activities aimed at preserving nature", "LanguageID" => 1]
            ],
                $missionStatementIds['animal_welfare'] ?? null => [
                ["MissionStatementName" => "Защита животных", "MissionStatementDescription" => "Защита и помощь животным", "LanguageID" => 2],
                ["MissionStatementName" => "Animal Welfare", "MissionStatementDescription" => "Protecting and assisting animals", "LanguageID" => 1]
            ],
                $missionStatementIds['education_support'] ?? null => [
                ["MissionStatementName" => "Поддержка образования", "MissionStatementDescription" => "Образовательные инициативы и программы", "LanguageID" => 2],
                ["MissionStatementName" => "Education Support", "MissionStatementDescription" => "Educational initiatives and programs", "LanguageID" => 1]
            ],
                $missionStatementIds['healthcare_initiatives'] ?? null => [
                ["MissionStatementName" => "Инициативы в здравоохранении", "MissionStatementDescription" => "Проекты, направленные на улучшение здравоохранения", "LanguageID" => 2],
                ["MissionStatementName" => "Healthcare Initiatives", "MissionStatementDescription" => "Projects aimed at improving healthcare", "LanguageID" => 1]
            ],
                $missionStatementIds['poverty_reduction'] ?? null => [
                ["MissionStatementName" => "Снижение бедности", "MissionStatementDescription" => "Инициативы по борьбе с бедностью", "LanguageID" => 2],
                ["MissionStatementName" => "Poverty Reduction", "MissionStatementDescription" => "Initiatives to fight poverty", "LanguageID" => 1]
            ],
                $missionStatementIds['disaster_relief'] ?? null => [
                ["MissionStatementName" => "Помощь в чрезвычайных ситуациях", "MissionStatementDescription" => "Помощь пострадавшим от стихийных бедствий", "LanguageID" => 2],
                ["MissionStatementName" => "Disaster Relief", "MissionStatementDescription" => "Assistance for victims of natural disasters", "LanguageID" => 1]
            ]
        ];

        $createdTranslations = [];

        // Создаём переводы для каждой должности, используя их ID
        foreach ($translationsData as $missionStatementIds => $translations) {
            if (!$missionStatementIds) {
                continue; // Пропускаем, если ID не найден
            }

            $missionStatement = $this->missionStatementRepository->findMissionStatementById($missionStatementIds);

            foreach ($translations as $translationData) {
                try {
                    $languageData = $this->languagesProxyService->validateLanguageID($translationData['LanguageID']);
                    $languageId = $languageData['LanguageID'];
                    $this->missionStatementValidationService->ensureUniqueTranslation($missionStatement, $languageId);

                    $translation = new MissionStatementTranslations();
                    $translation->setMissionStatementID($missionStatement);
                    $translation->setLanguageID($languageId);
                    $translation->setMissionStatementName($translationData['MissionStatementName']);
                    $translation->setMissionStatementDescription($translationData['MissionStatementDescription']);

                    $this->translationRepository->saveMissionStatementTranslations($translation, true);
                    $createdTranslations[] = $this->missionStatementValidationService->formatMissionStatementTranslationsData($translation);

                    $this->logger->info("Translation for MissionStatement ID '{$missionStatementIds}' and LanguageID '{$languageId}' created successfully.");
                } catch (\Exception $e) {
                    $this->logger->error("Failed to add translation for MissionStatement ID '$missionStatementIds': " . $e->getMessage());
                }
            }
        }

        return [
            'missionStatement' => $createdMissionStatement,
            'translations' => $createdTranslations,
            'message' => 'MissionStatement and translations seeded successfully.'
        ];
    }
}
