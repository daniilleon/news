<?php

namespace Module\Persons\MaritalStatus\Service;

use Module\Common\Helpers\FieldUpdateHelper;
use Module\Common\Service\ImageService;
use Module\Persons\MaritalStatus\Entity\MaritalStatus;
use Module\Persons\MaritalStatus\Entity\MaritalStatusTranslations;
use Module\Persons\MaritalStatus\Repository\MaritalStatusRepository;
use Module\Persons\MaritalStatus\Repository\MaritalStatusTranslationsRepository;
use Module\Common\Service\LanguagesProxyService;
use Psr\Log\LoggerInterface;

class MaritalStatusService
{
    private MaritalStatusRepository $maritalStatusRepository;
    private MaritalStatusTranslationsRepository $translationRepository;
    private LanguagesProxyService $languagesProxyService;
    private MaritalStatusValidationService $maritalStatusValidationService;
    private LoggerInterface $logger;
    private ImageService $imageService;
    private FieldUpdateHelper $helper;

    public function __construct(
        MaritalStatusRepository            $maritalStatusRepository,
        MaritalStatusTranslationsRepository $translationRepository,
        LanguagesProxyService $languagesProxyService,
        MaritalStatusValidationService     $maritalStatusValidationService,
        ImageService                           $imageService,
        FieldUpdateHelper                      $helper,
        LoggerInterface                        $logger
    ) {
        $this->maritalStatusRepository = $maritalStatusRepository;
        $this->translationRepository = $translationRepository;
        $this->languagesProxyService = $languagesProxyService;
        $this->maritalStatusValidationService = $maritalStatusValidationService;
        $this->imageService = $imageService;
        $this->helper = $helper;
        $this->logger = $logger;
    }


    /**
     * Получение всех MaritalStatus Статусов семейного положения.
     * @return array
     */
    public function getAllMaritalStatus(): array
    {
        try {
            $this->logger->info("Executing getAllMaritalStatus method.");
            $maritalStatus = $this->maritalStatusRepository->findAllMaritalStatus();

            // Проверка, есть ли языки
            if (empty($maritalStatus)) {
                $this->logger->info("No MaritalStatus found in the database.");
                return [
                    'maritalStatus' => [],
                    'message' => 'No MaritalStatus found in the database.'
                ];
            }
            // Форматируем каждый статус семейного положения и добавляем ключ для структурированного ответа
            return [
                'maritalStatus' => array_map([$this->maritalStatusValidationService, 'formatMaritalStatusData'], $maritalStatus),
                'message' => 'MaritalStatus retrieved successfully.'
            ];
        } catch (\InvalidArgumentException $e) {
            $this->logger->error("Validation error while fetching MaritalStatus: " . $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error("Unable to fetch languages: " . $e->getMessage());
            throw new \RuntimeException("Unable to fetch MaritalStatus at the moment.", 0, $e);
        }
    }

    /**
     * Получение Статуса семейного положения по ID вместе с переводами.
     *
     * @param int $id
     * @return array|null
     */
    public function getMaritalStatusById(int $id): ?array
    {
        $this->logger->info("Executing getMaritalStatusById method for ID: $id");
        // Используем validateMaritalStatusExists для получения Статуса семейного положения или выброса исключения
        $maritalStatus = $this->maritalStatusValidationService->validateMaritalStatusExists($id);
        $translations = $this->translationRepository->findTranslationsByMaritalStatus($maritalStatus);
        // Форматируем данные Статуса семейного положения и переводов
        return [
            'maritalStatus' => $this->maritalStatusValidationService->formatMaritalStatusData($maritalStatus),
            'translations' => array_map([$this->maritalStatusValidationService, 'formatMaritalStatusTranslationsData'], $translations),
            'message' => "MaritalStatus with ID $id retrieved successfully."
        ];
    }

    /**
     * Создание нового Статуса семейного положения.
     *
     * @param array $data
     * @return array
     */
    public function createMaritalStatus(array $data): array
    {
        $this->logger->info("Executing createMaritalStatus method.");
        try {
            // Преобразование кода в верхний регистр непосредственно перед присвоением
            if (isset($data['MaritalStatusCode'])) {
                $data['MaritalStatusCode'] = strtoupper($data['MaritalStatusCode']);
                $this->logger->info("MaritalStatusCode after strtoupper: " . $data['MaritalStatusCode']);
            }
            // Валидация данных для Статуса семейного положения
            $this->maritalStatusValidationService->validateMaritalStatusCode($data);
            $this->maritalStatusValidationService->ensureUniqueMaritalStatusCode($data['MaritalStatusCode'] ?? null);

            // Создаем новый Статус семейного положения
            $maritalStatus = new MaritalStatus();
            $this->helper->validateAndFilterFields($maritalStatus, $data);//проверяем список разрешенных полей
            $maritalStatus->setMaritalStatusCode($data['MaritalStatusCode']);

            // Сохраняем Статус семейного положения в репозитории
            $this->maritalStatusRepository->saveMaritalStatus($maritalStatus, true);
            $this->logger->info("MaritalStatus '{$maritalStatus->getMaritalStatusCode()}' created successfully.");

            // Форматируем ответ
            return [
                'maritalStatus' => $this->maritalStatusValidationService->formatMaritalStatusData($maritalStatus),
                'message' => 'MaritalStatus added successfully.'
            ];
        } catch (\InvalidArgumentException $e) {
            // Логируем ошибку валидации, чтобы избежать дублирования в контроллере
            $this->logger->error("Validation failed for creating MaritalStatus: " . $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            // Логируем общую ошибку и выбрасываем исключение
            $this->logger->error("An unexpected error occurred while creating MaritalStatus: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Добавление перевода для существующего Статуса семейного положения.
     *
     * @param int $maritalStatusId
     * @param array $data
     * @return array
     * @throws \InvalidArgumentException Если не удалось найти статус или язык
     * @throws \Exception Если произошла непредвиденная ошибка
     */
    public function createMaritalStatusTranslation(int $maritalStatusId, array $data): array
    {
        $this->logger->info("Executing createMaritalStatusTranslation method for MaritalStatus ID: $maritalStatusId.");
        try {
            // Проверяем существование Статуса семейного положения
            $maritalStatus = $this->maritalStatusValidationService->validateMaritalStatusExists($maritalStatusId);

            // Проверяем наличие выполняем валидацию
            $this->maritalStatusValidationService->validateMaritalStatusTranslationData($data);
            // Проверяем обязательность поля MaritalStatusName
            $this->maritalStatusValidationService->ensureUniqueMaritalStatusName($data['MaritalStatusName'] ?? null);
            // Проверка на наличие LanguageID и указать, что это обязательный параметр
            $languageData = $this->languagesProxyService->validateLanguageID($data['LanguageID']  ?? null);

            // Проверка уникальности перевода
            $this->maritalStatusValidationService->ensureUniqueTranslation($maritalStatus, $languageData['LanguageID']);

            // Создание нового перевода
            $translation = new MaritalStatusTranslations();
            $this->helper->validateAndFilterFields($translation, $data);//проверяем список разрешенных полей
            $translation->setMaritalStatusID($maritalStatus);
            $translation->setLanguageID($languageData['LanguageID']);

            // Применение дополнительных полей
            foreach ($data as $field => $value) {
                $setter = 'set' . ucfirst($field);
                if (method_exists($translation, $setter) && !in_array($field, ['MaritalStatusID', 'LanguageID'])) {
                    $translation->$setter($value);
                } elseif (!in_array($field, ['MaritalStatusID', 'LanguageID'])) {
                    throw new \InvalidArgumentException("Field '$field' does not exist on MaritalStatusTranslation entity.");
                }
            }
            $this->helper->validateAndFilterFields($translation, $data);//проверяем список разрешенных полей
            // Сохранение перевода
            $this->translationRepository->saveMaritalStatusTranslations($translation, true);
            $this->logger->info("Translation for MaritalStatus ID $maritalStatusId created successfully.");

            return [
                'maritalStatus' => $this->maritalStatusValidationService->formatMaritalStatusData($maritalStatus),
                'translation' => $this->maritalStatusValidationService->formatMaritalStatusTranslationsData($translation),
                'message' => 'MaritalStatus translation added successfully.'
            ];

        } catch (\InvalidArgumentException $e) {
            // Логируем и передаем исключение с детализированным сообщением
            $this->logger->error("Validation failed for MaritalStatus ID $maritalStatusId: " . $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            // Обработка неожиданных ошибок
            $this->logger->error("An unexpected error occurred while creating translation for MaritalStatus ID $maritalStatusId: " . $e->getMessage());
            throw new \RuntimeException("Unable to add MaritalStatus translation", 0, $e);
        }
    }

    //Обновление статуса семейного положения (Code статуса)
    public function updateMaritalStatusCode(int $maritalStatusId, array $data): array
    {
        $this->logger->info("Executing updateMaritalStatusCode method for MaritalStatus ID: $maritalStatusId");

        try {
            // Получаем Статус семейного положения по ID и проверяем его существование
            $maritalStatus = $this->maritalStatusValidationService->validateMaritalStatusExists($maritalStatusId);
            if (!$maritalStatus) {
                $this->logger->warning("MaritalStatus with ID $maritalStatusId not found for updating.");
                throw new \InvalidArgumentException("MaritalStatus with ID $maritalStatusId not found.");
            }
            // Преобразование кода в верхний регистр непосредственно перед присвоением
            if (isset($data['MaritalStatusCode'])) {
                $data['MaritalStatusCode'] = strtoupper($data['MaritalStatusCode']);
                $this->logger->info("MaritalStatusCode after strtoupper: " . $data['MaritalStatusCode']);
            }

            // Проверка наличия обязательных полей и их значений (в данных или в объекте)
            $requiredFields = ['MaritalStatusCode'];
            foreach ($requiredFields as $field) {
                $value = $data[$field] ?? $maritalStatus->{'get' . $field}();
                if (empty($value)) {
                    throw new \InvalidArgumentException("Field '$field' is required and cannot be empty.");
                }
            }

            // Используем FieldUpdateHelper для обновления поля MaritalStatusCode с проверками
            FieldUpdateHelper::updateFieldIfPresent(
                $maritalStatus,
                $data,
                'MaritalStatusCode',
                function ($newCode) use ($maritalStatusId) {
                    $this->maritalStatusValidationService->ensureUniqueMaritalStatusCode($newCode, $maritalStatusId);
                    $this->maritalStatusValidationService->validateMaritalStatusCode(['MaritalStatusCode' => $newCode]);
                }
            );

            $this->helper->validateAndFilterFields($maritalStatus, $data);//проверяем список разрешенных полей
            $this->maritalStatusRepository->saveMaritalStatus($maritalStatus, true);

            $this->logger->info("MaritalStatus Code updated successfully for MaritalStatus ID: $maritalStatusId");

            return [
                'maritalStatus' => $this->maritalStatusValidationService->formatMaritalStatusData($maritalStatus),
                'message' => 'MaritalStatus Code updated successfully.'
            ];
        } catch (\InvalidArgumentException $e) {
            $this->logger->error("Validation failed for MaritalStatus ID $maritalStatusId: " . $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error("An unexpected error occurred while updating MaritalStatus Code for ID $maritalStatusId: " . $e->getMessage());
            throw new \RuntimeException("Unable to update MaritalStatus Code", 0, $e);
        }
    }


    //Обновление переводов у Статуса семейного положения
    public function updateMaritalStatusTranslation(int $maritalStatusId, int $translationId, array $data): array
    {
        $this->logger->info("Updating translation for MaritalStatus ID: $maritalStatusId and Translation ID: $translationId");

        try {
            $maritalStatus = $this->maritalStatusValidationService->validateMaritalStatusExists($maritalStatusId);

            // Поиск перевода по ID
            $translation = $this->translationRepository->find($translationId);
            if (!$translation || $translation->getMaritalStatusID()->getMaritalStatusID() !== $maritalStatusId) {
                $this->logger->error("Translation for MaritalStatus ID $maritalStatusId and Translation ID $translationId not found.");
                throw new \InvalidArgumentException("Translation with this ID does not exist for this MaritalStatus.");
            }

            // Проверка, что LanguageID не был передан в запросе
            $this->languagesProxyService->checkImmutableLanguageID($data, $translation->getLanguageID());

            // Проверка наличия обязательных полей и их значений (в данных или в объекте)
            $requiredFields = ['MaritalStatusName'];
            foreach ($requiredFields as $field) {
                $value = $data[$field] ?? $translation->{'get' . $field}();
                if (empty($value)) {
                    throw new \InvalidArgumentException("Field '$field' is required and cannot be empty.");
                }
            }
            // Валидация всех данных, переданных в $data
            $this->maritalStatusValidationService->validateMaritalStatusTranslationData($data);

            // Обновление поля MaritalStatusName с проверкой уникальности и наличия
            FieldUpdateHelper::updateFieldIfPresent(
                $translation,
                $data,
                'MaritalStatusName',
                function ($newName) use ($translationId) {
                    $this->maritalStatusValidationService->ensureUniqueMaritalStatusName($newName, $translationId);
                }
            );

            // Обновление полей перевода, игнорируя LanguageID
            foreach ($data as $field => $value) {
                $setter = 'set' . ucfirst($field);
                if (method_exists($translation, $setter) && !in_array($field, ['MaritalStatusID'])) {
                    $translation->$setter($value);
                } elseif (!in_array($field, ['MaritalStatusID'])) {
                    throw new \InvalidArgumentException("Field '$field' does not exist on MaritalStatusTranslation entity.");
                }
            }
            $this->helper->validateAndFilterFields($translation, $data);//проверяем список разрешенных полей
            $this->translationRepository->saveMaritalStatusTranslations($translation, true);
            $this->logger->info("Translation updated successfully for MaritalStatus ID: $maritalStatusId and Translation ID: $translationId");

            return [
                'maritalStatus' => $this->maritalStatusValidationService->formatMaritalStatusData($maritalStatus),
                'translation' => $this->maritalStatusValidationService->formatMaritalStatusTranslationsData($translation),
                'message' => 'MaritalStatus translation updated successfully.'
            ];
        } catch (\InvalidArgumentException $e) {
            $this->logger->error("Validation failed for MaritalStatus ID $maritalStatusId and Translation ID $translationId: " . $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error("An unexpected error occurred while updating translation for MaritalStatus ID $maritalStatusId and Translation ID $translationId: " . $e->getMessage());
            throw new \RuntimeException("Unable to update MaritalStatus translation", 0, $e);
        }
    }

    /**
     * Удаление перевода Статуса семейного положения.
     *
     * @param int $maritalStatusId
     * @param int $translationId
     * @return array
     */
    public function deleteMaritalStatusTranslation(int $maritalStatusId, int $translationId): array
    {
        $this->logger->info("Deleting translation with ID: $translationId for MaritalStatus ID: $maritalStatusId");

        try {
            // Проверка существования Статуса семейного положения
            $this->maritalStatusValidationService->validateMaritalStatusExists($maritalStatusId);

            // Проверка существования перевода для данного Статуса семейного положения
            $translation = $this->translationRepository->find($translationId);
            if (!$translation || $translation->getMaritalStatusID()->getMaritalStatusID() !== $maritalStatusId) {
                $this->logger->error("Translation with ID $translationId does not exist for MaritalStatus ID $maritalStatusId.");
                throw new \InvalidArgumentException("Translation with this ID does not exist for this MaritalStatus.");
            }

            // Удаление перевода
            $this->translationRepository->deleteMaritalStatusTranslations($translation, true);
            $this->logger->info("Translation with ID $translationId successfully deleted for MaritalStatus ID $maritalStatusId.");

            return [
                'message' => "Translation with ID $translationId successfully deleted for MaritalStatus ID $maritalStatusId.",
            ];
        } catch (\InvalidArgumentException $e) {
            $this->logger->warning($e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error("An unexpected error occurred while deleting translation with ID $translationId for MaritalStatus ID $maritalStatusId: " . $e->getMessage());
            throw new \RuntimeException("Unable to delete MaritalStatus translation", 0, $e);
        }
    }


    /**
     * Удаление Статуса семейного положения.
     *
     * @param int $maritalStatusId
     * @return array
     */
    public function deleteMaritalStatus(int $maritalStatusId): array
    {
        try {
            $this->logger->info("Executing deleteMaritalStatus method for ID: $maritalStatusId");

            $maritalStatus = $this->maritalStatusValidationService->validateMaritalStatusExists($maritalStatusId);


            // Удаляем переводы Статуса семейного положения
            $translations = $this->translationRepository->findTranslationsByMaritalStatus($maritalStatus);
            foreach ($translations as $translation) {
                $this->translationRepository->deleteMaritalStatusTranslations($translation, true);
            }

            // Удаляем сам Статус семейного положения
            $this->maritalStatusRepository->deleteMaritalStatus($maritalStatus, true);
            $this->logger->info("MaritalStatus with ID $maritalStatusId and its translations successfully deleted.");

            return [
                'message' => "MaritalStatus with ID $maritalStatusId and its translations successfully deleted.",
            ];
        } catch (\InvalidArgumentException $e) {
            $this->logger->warning($e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error("An unexpected error occurred while deleting MaritalStatus with ID $maritalStatusId: " . $e->getMessage());
            throw $e;
        }
    }

    public function seedMaritalStatusAndTranslations(): array
    {
        $this->logger->info("Executing seedMaritalStatusAndTranslations method.");

        // Данные для предустановленных Статусов семейного положения
        $maritalStatusData = [
            ["MaritalStatusCode" => "MARRIED"],
            ["MaritalStatusCode" => "DIVORCED"],
            ["MaritalStatusCode" => "WIDOWED"],
            ["MaritalStatusCode" => "SINGLE"]
        ];

        $createdMaritalStatus = [];
        $maritalStatusIds = [];

        // Создаём Статусы семейного положения и сохраняем их ID
        foreach ($maritalStatusData as $maritalData) {
            try {
                $this->maritalStatusValidationService->validateMaritalStatusCode($maritalData);
                $this->maritalStatusValidationService->ensureUniqueMaritalStatusCode($maritalData['MaritalStatusCode']);

                $maritalStatus = new MaritalStatus();
                $maritalStatus->setMaritalStatusCode($maritalData['MaritalStatusCode']);
                $this->maritalStatusRepository->saveMaritalStatus($maritalStatus, true);

                $createdMaritalStatus[] = $this->maritalStatusValidationService->formatMaritalStatusData($maritalStatus);
                $maritalStatusIds[$maritalData['MaritalStatusCode']] = $maritalStatus->getMaritalStatusID(); // Сохраняем ID Статуса семейного положения

                $this->logger->info("Marital Status '{$maritalData['MaritalStatusCode']}' created successfully.");
            } catch (\InvalidArgumentException $e) {
                $this->logger->warning("Marital Status '{$maritalData['MaritalStatusCode']}' already exists or is invalid. Skipping.");
            }
        }

        // Данные для переводов Статусов семейного положения, привязанные к MaritalStatusID
        $translationsData = [
                $maritalStatusIds['MARRIED'] ?? null => [
                ["MaritalStatusName" => "Женат", "LanguageID" => 2],
                ["MaritalStatusName" => "Married", "LanguageID" => 1]
            ],
                $maritalStatusIds['DIVORCED'] ?? null => [
                ["MaritalStatusName" => "Разведен", "LanguageID" => 2],
                ["MaritalStatusName" => "Divorced", "LanguageID" => 1]
            ],
                $maritalStatusIds['WIDOWED'] ?? null => [
                ["MaritalStatusName" => "Вдовец/Вдова", "LanguageID" => 2],
                ["MaritalStatusName" => "Widowed", "LanguageID" => 1]
            ],
                $maritalStatusIds['SINGLE'] ?? null => [
                ["MaritalStatusName" => "Свободен/Свободна", "LanguageID" => 2],
                ["MaritalStatusName" => "Single", "LanguageID" => 1]
            ]
        ];

        $createdTranslations = [];

        // Создаём переводы для каждого Статуса семейного положения, используя их ID
        foreach ($translationsData as $maritalStatusId => $translations) {
            if (!$maritalStatusId) {
                continue; // Пропускаем, если ID не найден
            }

            $maritalStatus = $this->maritalStatusRepository->findMaritalStatusById($maritalStatusId);

            foreach ($translations as $translationData) {
                try {
                    $languageData = $this->languagesProxyService->validateLanguageID($translationData['LanguageID']);
                    $languageId = $languageData['LanguageID'];
                    $this->maritalStatusValidationService->ensureUniqueTranslation($maritalStatus, $languageId);

                    $translation = new MaritalStatusTranslations();
                    $translation->setMaritalStatusID($maritalStatus);
                    $translation->setLanguageID($languageId);
                    $translation->setMaritalStatusName($translationData['MaritalStatusName']);

                    $this->translationRepository->saveMaritalStatusTranslations($translation, true);
                    $createdTranslations[] = $this->maritalStatusValidationService->formatMaritalStatusTranslationsData($translation);

                    $this->logger->info("Translation for MaritalStatus ID '{$maritalStatusId}' and LanguageID '{$languageId}' created successfully.");
                } catch (\Exception $e) {
                    $this->logger->error("Failed to add translation for MaritalStatus ID '$maritalStatusId': " . $e->getMessage());
                }
            }
        }

        return [
            'maritalStatus' => $createdMaritalStatus,
            'translations' => $createdTranslations,
            'message' => 'MaritalStatus and translations seeded successfully.'
        ];
    }

}
