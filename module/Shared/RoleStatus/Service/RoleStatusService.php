<?php

namespace Module\Shared\RoleStatus\Service;

use Module\Common\Helpers\FieldUpdateHelper;
use Module\Common\Service\ImageService;
use Module\Shared\RoleStatus\Entity\RoleStatus;
use Module\Shared\RoleStatus\Entity\RoleStatusTranslations;
use Module\Shared\RoleStatus\Repository\RoleStatusRepository;
use Module\Shared\RoleStatus\Repository\RoleStatusTranslationsRepository;
use Module\Common\Proxy\Core\LanguagesProxyService;
use Module\Shared\RoleStatus\Service\RoleStatusValidationService;
use Psr\Log\LoggerInterface;

class RoleStatusService
{
    private RoleStatusRepository $roleStatusRepository;
    private RoleStatusTranslationsRepository $translationRepository;
    private LanguagesProxyService $languagesProxyService;
    private RoleStatusValidationService $roleStatusValidationService;
    private LoggerInterface $logger;
    private ImageService $imageService;
    private FieldUpdateHelper $helper;

    public function __construct(
        RoleStatusRepository            $roleStatusRepository,
        RoleStatusTranslationsRepository $translationRepository,
        LanguagesProxyService               $languagesProxyService,
        RoleStatusValidationService     $roleStatusValidationService,
        ImageService                           $imageService,
        FieldUpdateHelper                      $helper,
        LoggerInterface                        $logger
    ) {
        $this->roleStatusRepository = $roleStatusRepository;
        $this->translationRepository = $translationRepository;
        $this->languagesProxyService = $languagesProxyService;
        $this->roleStatusValidationService = $roleStatusValidationService;
        $this->imageService = $imageService;
        $this->helper = $helper;
        $this->logger = $logger;
    }


    /**
     * Получение всех RoleStatus Статусов семейного положения.
     * @return array
     */
    public function getAllRoleStatus(): array
    {
        try {
            $this->logger->info("Executing getAllRoleStatus method.");
            $roleStatus = $this->roleStatusRepository->findAllRoleStatus();

            // Проверка, есть ли языки
            if (empty($roleStatus)) {
                $this->logger->info("No RoleStatus found in the database.");
                return [
                    'roleStatus' => [],
                    'message' => 'No RoleStatus found in the database.'
                ];
            }
            // Форматируем каждый статус семейного положения и добавляем ключ для структурированного ответа
            return [
                'roleStatus' => array_map([$this->roleStatusValidationService, 'formatRoleStatusData'], $roleStatus),
                'message' => 'RoleStatus retrieved successfully.'
            ];
        } catch (\InvalidArgumentException $e) {
            $this->logger->error("Validation error while fetching RoleStatus: " . $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error("Unable to fetch languages: " . $e->getMessage());
            throw new \RuntimeException("Unable to fetch RoleStatus at the moment.", 0, $e);
        }
    }

    /**
     * Получение Статуса семейного положения по ID вместе с переводами.
     *
     * @param int $id
     * @return array|null
     */
    public function getRoleStatusById(int $id): ?array
    {
        $this->logger->info("Executing getRoleStatusById method for ID: $id");
        // Используем validateRoleStatusExists для получения Статуса семейного положения или выброса исключения
        $roleStatus = $this->roleStatusValidationService->validateRoleStatusExists($id);
        $translations = $this->translationRepository->findTranslationsByRoleStatus($roleStatus);
        // Форматируем данные Статуса семейного положения и переводов
        return [
            'roleStatus' => $this->roleStatusValidationService->formatRoleStatusData($roleStatus),
            'translations' => array_map([$this->roleStatusValidationService, 'formatRoleStatusTranslationsData'], $translations),
            'message' => "RoleStatus with ID $id retrieved successfully."
        ];
    }

    /**
     * Создание нового Статуса семейного положения.
     *
     * @param array $data
     * @return array
     */
    public function createRoleStatus(array $data): array
    {
        $this->logger->info("Executing createRoleStatus method.");
        try {
            // Преобразование кода в верхний регистр непосредственно перед присвоением
            if (isset($data['RoleStatusCode'])) {
                $data['RoleStatusCode'] = strtoupper($data['RoleStatusCode']);
                $this->logger->info("RoleStatusCode after strtoupper: " . $data['RoleStatusCode']);
            }
            // Валидация данных для Статуса семейного положения
            $this->roleStatusValidationService->validateRoleStatusCode($data);
            $this->roleStatusValidationService->ensureUniqueRoleStatusCode($data['RoleStatusCode'] ?? null);

            // Создаем новый Статус семейного положения
            $roleStatus = new RoleStatus();
            $this->helper->validateAndFilterFields($roleStatus, $data);//проверяем список разрешенных полей
            $roleStatus->setRoleStatusCode($data['RoleStatusCode']);

            // Сохраняем Статус семейного положения в репозитории
            $this->roleStatusRepository->saveRoleStatus($roleStatus, true);
            $this->logger->info("RoleStatus '{$roleStatus->getRoleStatusCode()}' created successfully.");

            // Форматируем ответ
            return [
                'roleStatus' => $this->roleStatusValidationService->formatRoleStatusData($roleStatus),
                'message' => 'RoleStatus added successfully.'
            ];
        } catch (\InvalidArgumentException $e) {
            // Логируем ошибку валидации, чтобы избежать дублирования в контроллере
            $this->logger->error("Validation failed for creating RoleStatus: " . $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            // Логируем общую ошибку и выбрасываем исключение
            $this->logger->error("An unexpected error occurred while creating RoleStatus: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Добавление перевода для существующего Статуса семейного положения.
     *
     * @param int $roleStatusId
     * @param array $data
     * @return array
     * @throws \InvalidArgumentException Если не удалось найти статус или язык
     * @throws \Exception Если произошла непредвиденная ошибка
     */
    public function createRoleStatusTranslation(int $roleStatusId, array $data): array
    {
        $this->logger->info("Executing createRoleStatusTranslation method for RoleStatus ID: $roleStatusId.");
        try {
            // Проверяем существование Статуса семейного положения
            $roleStatus = $this->roleStatusValidationService->validateRoleStatusExists($roleStatusId);

            // Проверяем наличие выполняем валидацию
            $this->roleStatusValidationService->validateRoleStatusTranslationData($data);
            // Проверяем обязательность поля RoleStatusName
            $this->roleStatusValidationService->ensureUniqueRoleStatusName($data['RoleStatusName'] ?? null);
            // Проверка на наличие LanguageID и указать, что это обязательный параметр
            $languageData = $this->languagesProxyService->validateLanguageID($data['LanguageID']  ?? null);

            // Проверка уникальности перевода
            $this->roleStatusValidationService->ensureUniqueTranslation($roleStatus, $languageData['LanguageID']);

            // Создание нового перевода
            $translation = new RoleStatusTranslations();
            $this->helper->validateAndFilterFields($translation, $data);//проверяем список разрешенных полей
            $translation->setRoleStatusID($roleStatus);
            $translation->setLanguageID($languageData['LanguageID']);

            // Применение дополнительных полей
            foreach ($data as $field => $value) {
                $setter = 'set' . ucfirst($field);
                if (method_exists($translation, $setter) && !in_array($field, ['RoleStatusID', 'LanguageID'])) {
                    $translation->$setter($value);
                } elseif (!in_array($field, ['RoleStatusID', 'LanguageID'])) {
                    throw new \InvalidArgumentException("Field '$field' does not exist on RoleStatusTranslation entity.");
                }
            }
            $this->helper->validateAndFilterFields($translation, $data);//проверяем список разрешенных полей
            // Сохранение перевода
            $this->translationRepository->saveRoleStatusTranslations($translation, true);
            $this->logger->info("Translation for RoleStatus ID $roleStatusId created successfully.");

            return [
                'roleStatus' => $this->roleStatusValidationService->formatRoleStatusData($roleStatus),
                'translation' => $this->roleStatusValidationService->formatRoleStatusTranslationsData($translation),
                'message' => 'RoleStatus translation added successfully.'
            ];

        } catch (\InvalidArgumentException $e) {
            // Логируем и передаем исключение с детализированным сообщением
            $this->logger->error("Validation failed for RoleStatus ID $roleStatusId: " . $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            // Обработка неожиданных ошибок
            $this->logger->error("An unexpected error occurred while creating translation for RoleStatus ID $roleStatusId: " . $e->getMessage());
            throw new \RuntimeException("Unable to add RoleStatus translation", 0, $e);
        }
    }

    //Обновление статуса семейного положения (Code статуса)
    public function updateRoleStatusCode(int $roleStatusId, array $data): array
    {
        $this->logger->info("Executing updateRoleStatusCode method for RoleStatus ID: $roleStatusId");

        try {
            // Получаем Статус семейного положения по ID и проверяем его существование
            $roleStatus = $this->roleStatusValidationService->validateRoleStatusExists($roleStatusId);
            if (!$roleStatus) {
                $this->logger->warning("RoleStatus with ID $roleStatusId not found for updating.");
                throw new \InvalidArgumentException("RoleStatus with ID $roleStatusId not found.");
            }
            // Преобразование кода в верхний регистр непосредственно перед присвоением
            if (isset($data['RoleStatusCode'])) {
                $data['RoleStatusCode'] = strtoupper($data['RoleStatusCode']);
                $this->logger->info("RoleStatusCode after strtoupper: " . $data['RoleStatusCode']);
            }

            // Проверка наличия обязательных полей и их значений (в данных или в объекте)
            $requiredFields = ['RoleStatusCode'];
            foreach ($requiredFields as $field) {
                $value = $data[$field] ?? $roleStatus->{'get' . $field}();
                if (empty($value)) {
                    throw new \InvalidArgumentException("Field '$field' is required and cannot be empty.");
                }
            }

            // Используем FieldUpdateHelper для обновления поля RoleStatusCode с проверками
            FieldUpdateHelper::updateFieldIfPresent(
                $roleStatus,
                $data,
                'RoleStatusCode',
                function ($newCode) use ($roleStatusId) {
                    $this->roleStatusValidationService->ensureUniqueRoleStatusCode($newCode, $roleStatusId);
                    $this->roleStatusValidationService->validateRoleStatusCode(['RoleStatusCode' => $newCode]);
                }
            );

            $this->helper->validateAndFilterFields($roleStatus, $data);//проверяем список разрешенных полей
            $this->roleStatusRepository->saveRoleStatus($roleStatus, true);

            $this->logger->info("RoleStatus Code updated successfully for RoleStatus ID: $roleStatusId");

            return [
                'roleStatus' => $this->roleStatusValidationService->formatRoleStatusData($roleStatus),
                'message' => 'RoleStatus Code updated successfully.'
            ];
        } catch (\InvalidArgumentException $e) {
            $this->logger->error("Validation failed for RoleStatus ID $roleStatusId: " . $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error("An unexpected error occurred while updating RoleStatus Code for ID $roleStatusId: " . $e->getMessage());
            throw new \RuntimeException("Unable to update RoleStatus Code", 0, $e);
        }
    }


    //Обновление переводов у Статуса семейного положения
    public function updateRoleStatusTranslation(int $roleStatusId, int $translationId, array $data): array
    {
        $this->logger->info("Updating translation for RoleStatus ID: $roleStatusId and Translation ID: $translationId");

        try {
            $roleStatus = $this->roleStatusValidationService->validateRoleStatusExists($roleStatusId);

            // Поиск перевода по ID
            $translation = $this->translationRepository->find($translationId);
            if (!$translation || $translation->getRoleStatusID()->getRoleStatusID() !== $roleStatusId) {
                $this->logger->error("Translation for RoleStatus ID $roleStatusId and Translation ID $translationId not found.");
                throw new \InvalidArgumentException("Translation with this ID does not exist for this RoleStatus.");
            }

            // Проверка, что LanguageID не был передан в запросе
            $this->languagesProxyService->checkImmutableLanguageID($data, $translation->getLanguageID());

            // Проверка наличия обязательных полей и их значений (в данных или в объекте)
            $requiredFields = ['RoleStatusName'];
            foreach ($requiredFields as $field) {
                $value = $data[$field] ?? $translation->{'get' . $field}();
                if (empty($value)) {
                    throw new \InvalidArgumentException("Field '$field' is required and cannot be empty.");
                }
            }
            // Валидация всех данных, переданных в $data
            $this->roleStatusValidationService->validateRoleStatusTranslationData($data);

            // Обновление поля RoleStatusName с проверкой уникальности и наличия
            FieldUpdateHelper::updateFieldIfPresent(
                $translation,
                $data,
                'RoleStatusName',
                function ($newName) use ($translationId) {
                    $this->roleStatusValidationService->ensureUniqueRoleStatusName($newName, $translationId);
                }
            );

            // Обновление полей перевода, игнорируя LanguageID
            foreach ($data as $field => $value) {
                $setter = 'set' . ucfirst($field);
                if (method_exists($translation, $setter) && !in_array($field, ['RoleStatusID'])) {
                    $translation->$setter($value);
                } elseif (!in_array($field, ['RoleStatusID'])) {
                    throw new \InvalidArgumentException("Field '$field' does not exist on RoleStatusTranslation entity.");
                }
            }
            $this->helper->validateAndFilterFields($translation, $data);//проверяем список разрешенных полей
            $this->translationRepository->saveRoleStatusTranslations($translation, true);
            $this->logger->info("Translation updated successfully for RoleStatus ID: $roleStatusId and Translation ID: $translationId");

            return [
                'roleStatus' => $this->roleStatusValidationService->formatRoleStatusData($roleStatus),
                'translation' => $this->roleStatusValidationService->formatRoleStatusTranslationsData($translation),
                'message' => 'RoleStatus translation updated successfully.'
            ];
        } catch (\InvalidArgumentException $e) {
            $this->logger->error("Validation failed for RoleStatus ID $roleStatusId and Translation ID $translationId: " . $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error("An unexpected error occurred while updating translation for RoleStatus ID $roleStatusId and Translation ID $translationId: " . $e->getMessage());
            throw new \RuntimeException("Unable to update RoleStatus translation", 0, $e);
        }
    }

    /**
     * Удаление перевода Статуса семейного положения.
     *
     * @param int $roleStatusId
     * @param int $translationId
     * @return array
     */
    public function deleteRoleStatusTranslation(int $roleStatusId, int $translationId): array
    {
        $this->logger->info("Deleting translation with ID: $translationId for RoleStatus ID: $roleStatusId");

        try {
            // Проверка существования Статуса семейного положения
            $this->roleStatusValidationService->validateRoleStatusExists($roleStatusId);

            // Проверка существования перевода для данного Статуса семейного положения
            $translation = $this->translationRepository->find($translationId);
            if (!$translation || $translation->getRoleStatusID()->getRoleStatusID() !== $roleStatusId) {
                $this->logger->error("Translation with ID $translationId does not exist for RoleStatus ID $roleStatusId.");
                throw new \InvalidArgumentException("Translation with this ID does not exist for this RoleStatus.");
            }

            // Удаление перевода
            $this->translationRepository->deleteRoleStatusTranslations($translation, true);
            $this->logger->info("Translation with ID $translationId successfully deleted for RoleStatus ID $roleStatusId.");

            return [
                'message' => "Translation with ID $translationId successfully deleted for RoleStatus ID $roleStatusId.",
            ];
        } catch (\InvalidArgumentException $e) {
            $this->logger->warning($e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error("An unexpected error occurred while deleting translation with ID $translationId for RoleStatus ID $roleStatusId: " . $e->getMessage());
            throw new \RuntimeException("Unable to delete RoleStatus translation", 0, $e);
        }
    }


    /**
     * Удаление Статуса семейного положения.
     *
     * @param int $roleStatusId
     * @return array
     */
    public function deleteRoleStatus(int $roleStatusId): array
    {
        try {
            $this->logger->info("Executing deleteRoleStatus method for ID: $roleStatusId");

            $roleStatus = $this->roleStatusValidationService->validateRoleStatusExists($roleStatusId);


            // Удаляем переводы Статуса семейного положения
            $translations = $this->translationRepository->findTranslationsByRoleStatus($roleStatus);
            foreach ($translations as $translation) {
                $this->translationRepository->deleteRoleStatusTranslations($translation, true);
            }

            // Удаляем сам Статус семейного положения
            $this->roleStatusRepository->deleteRoleStatus($roleStatus, true);
            $this->logger->info("RoleStatus with ID $roleStatusId and its translations successfully deleted.");

            return [
                'message' => "RoleStatus with ID $roleStatusId and its translations successfully deleted.",
            ];
        } catch (\InvalidArgumentException $e) {
            $this->logger->warning($e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error("An unexpected error occurred while deleting RoleStatus with ID $roleStatusId: " . $e->getMessage());
            throw $e;
        }
    }

    public function seedRoleStatusAndTranslations(): array
    {
        $this->logger->info("Executing seedRoleStatusAndTranslations method.");

        // Данные для предустановленных Статусов семейного положения
        $roleStatusData = [
            ["RoleStatusCode" => "CEO"],
            ["RoleStatusCode" => "FOUNDER"],
            ["RoleStatusCode" => "INVESTOR"],
            ["RoleStatusCode" => "INHERITOR"],
            ["RoleStatusCode" => "BOARD_MEMBER"],
            ["RoleStatusCode" => "ADVISOR"],
            ["RoleStatusCode" => "PARTNER"],
            ["RoleStatusCode" => "OTHER"]
        ];

        $createdRoleStatus = [];
        $roleStatusIds = [];

        // Создаём Статусы семейного положения и сохраняем их ID
        foreach ($roleStatusData as $roleData) {
            try {
                $this->roleStatusValidationService->validateRoleStatusCode($roleData);
                $this->roleStatusValidationService->ensureUniqueRoleStatusCode($roleData['RoleStatusCode']);

                $roleStatus = new RoleStatus();
                $roleStatus->setRoleStatusCode($roleData['RoleStatusCode']);
                $this->roleStatusRepository->saveRoleStatus($roleStatus, true);

                $createdRoleStatus[] = $this->roleStatusValidationService->formatRoleStatusData($roleStatus);
                $roleStatusIds[$roleData['RoleStatusCode']] = $roleStatus->getRoleStatusID(); // Сохраняем ID Статуса семейного положения

                $this->logger->info("Marital Status '{$roleData['RoleStatusCode']}' created successfully.");
            } catch (\InvalidArgumentException $e) {
                $this->logger->warning("Marital Status '{$roleData['RoleStatusCode']}' already exists or is invalid. Skipping.");
            }
        }

        // Данные для переводов Статусов семейного положения, привязанные к RoleStatusID
        $translationsData = [
            $roleStatusIds['CEO'] ?? null => [
                ["RoleStatusName" => "Генеральный директор", "LanguageID" => 2],
                ["RoleStatusName" => "Chief Executive Officer", "LanguageID" => 1]
            ],
            $roleStatusIds['FOUNDER'] ?? null => [
                ["RoleStatusName" => "Основатель", "LanguageID" => 2],
                ["RoleStatusName" => "Founder", "LanguageID" => 1]
            ],
            $roleStatusIds['INVESTOR'] ?? null => [
                ["RoleStatusName" => "Инвестор", "LanguageID" => 2],
                ["RoleStatusName" => "Investor", "LanguageID" => 1]
            ],
            $roleStatusIds['INHERITOR'] ?? null => [
                ["RoleStatusName" => "Наследник", "LanguageID" => 2],
                ["RoleStatusName" => "Inheritor", "LanguageID" => 1]
            ],
            $roleStatusIds['BOARD_MEMBER'] ?? null => [
                ["RoleStatusName" => "Член совета директоров", "LanguageID" => 2],
                ["RoleStatusName" => "Board Member", "LanguageID" => 1]
            ],
            $roleStatusIds['ADVISOR'] ?? null => [
                ["RoleStatusName" => "Советник", "LanguageID" => 2],
                ["RoleStatusName" => "Advisor", "LanguageID" => 1]
            ],
            $roleStatusIds['PARTNER'] ?? null => [
                ["RoleStatusName" => "Партнер", "LanguageID" => 2],
                ["RoleStatusName" => "Partner", "LanguageID" => 1]
            ],
            $roleStatusIds['OTHER'] ?? null => [
                ["RoleStatusName" => "Другое", "LanguageID" => 2],
                ["RoleStatusName" => "Other", "LanguageID" => 1]
            ]
        ];

        $createdTranslations = [];

        // Создаём переводы для каждого Статуса семейного положения, используя их ID
        foreach ($translationsData as $roleStatusId => $translations) {
            if (!$roleStatusId) {
                continue; // Пропускаем, если ID не найден
            }

            $roleStatus = $this->roleStatusRepository->findRoleStatusById($roleStatusId);

            foreach ($translations as $translationData) {
                try {
                    $languageData = $this->languagesProxyService->validateLanguageID($translationData['LanguageID']);
                    $languageId = $languageData['LanguageID'];
                    $this->roleStatusValidationService->ensureUniqueTranslation($roleStatus, $languageId);

                    $translation = new RoleStatusTranslations();
                    $translation->setRoleStatusID($roleStatus);
                    $translation->setLanguageID($languageId);
                    $translation->setRoleStatusName($translationData['RoleStatusName']);

                    $this->translationRepository->saveRoleStatusTranslations($translation, true);
                    $createdTranslations[] = $this->roleStatusValidationService->formatRoleStatusTranslationsData($translation);

                    $this->logger->info("Translation for RoleStatus ID '{$roleStatusId}' and LanguageID '{$languageId}' created successfully.");
                } catch (\Exception $e) {
                    $this->logger->error("Failed to add translation for RoleStatus ID '$roleStatusId': " . $e->getMessage());
                }
            }
        }

        return [
            'roleStatus' => $createdRoleStatus,
            'translations' => $createdTranslations,
            'message' => 'RoleStatus and translations seeded successfully.'
        ];
    }

}
