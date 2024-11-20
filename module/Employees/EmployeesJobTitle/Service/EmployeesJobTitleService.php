<?php

namespace Module\Employees\EmployeesJobTitle\Service;

use Module\Common\Helpers\FieldUpdateHelper;
use Module\Common\Service\ImageService;
use Module\Employees\EmployeesJobTitle\Entity\EmployeeJobTitleTranslations;
use Module\Employees\EmployeesJobTitle\Entity\EmployeesJobTitle;
use Module\Employees\EmployeesJobTitle\Repository\EmployeeJobTitleTranslationsRepository;
use Module\Employees\EmployeesJobTitle\Repository\EmployeesJobTitleRepository;
use Module\Employees\EmployeesJobTitle\Service\EmployeesJobTitleValidationService;
use Module\Common\Proxy\Core\LanguagesProxyService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class EmployeesJobTitleService
{
    private EmployeesJobTitleRepository $employeesJobTitleRepository;
    private EmployeeJobTitleTranslationsRepository $translationRepository;
    private LanguagesProxyService $languagesProxyService;
    private EmployeesJobTitleValidationService $employeesJobTitleValidationService;
    private LoggerInterface $logger;
    private ImageService $imageService;
    private FieldUpdateHelper $helper;

    public function __construct(
        EmployeesJobTitleRepository            $employeesJobTitleRepository,
        EmployeeJobTitleTranslationsRepository $translationRepository,
        LanguagesProxyService         $languagesProxyService,
        EmployeesJobTitleValidationService     $employeesJobTitleValidationService,
        ImageService                           $imageService,
        FieldUpdateHelper                      $helper,
        LoggerInterface                        $logger
    ) {
        $this->employeesJobTitleRepository = $employeesJobTitleRepository;
        $this->translationRepository = $translationRepository;
        $this->languagesProxyService = $languagesProxyService;
        $this->employeesJobTitleValidationService = $employeesJobTitleValidationService;
        $this->imageService = $imageService;
        $this->helper = $helper;
        $this->logger = $logger;
    }


    /**
     * Получение всех EmployeesJobTitle должностей.
     * @return array
     */
    public function getAllEmployeesJobTitle(): array
    {
        try {
            $this->logger->info("Executing getAllEmployeesJobTitle method.");
            $employeesJobTitle = $this->employeesJobTitleRepository->findAllEmployeesJobTitle();

            // Проверка, есть ли языки
            if (empty($employeesJobTitle)) {
                $this->logger->info("No EmployeesJobTitle found in the database.");
                return [
                    'employeesJobTitle' => [],
                    'message' => 'No EmployeesJobTitle found in the database.'
                ];
            }
            // Форматируем каждую должность и добавляем ключ для структурированного ответа
            return [
                'employeesJobTitle' => array_map([$this->employeesJobTitleValidationService, 'formatEmployeesJobTitleData'], $employeesJobTitle),
                'message' => 'EmployeesJobTitle retrieved successfully.'
            ];
        } catch (\InvalidArgumentException $e) {
            $this->logger->error("Validation error while fetching EmployeesJobTitle: " . $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error("Unable to fetch languages: " . $e->getMessage());
            throw new \RuntimeException("Unable to fetch EmployeesJobTitle at the moment.", 0, $e);
        }
    }

    /**
     * Получение Должности по ID вместе с переводами.
     *
     * @param int $id
     * @return array|null
     */
    public function getEmployeeJobTitleById(int $id): ?array
    {
        $this->logger->info("Executing getEmployeeJobTitleById method for ID: $id");
        // Используем validateEmployeeJobTitleExists для получения должностей или выброса исключения
        $employeeJobTitle = $this->employeesJobTitleValidationService->validateEmployeeJobTitleExists($id);
        $translations = $this->translationRepository->findTranslationsByEmployeesJobTitle($employeeJobTitle);
        // Форматируем данные должности и переводов
        return [
            'employeeJobTitle' => $this->employeesJobTitleValidationService->formatEmployeesJobTitleData($employeeJobTitle),
            'translations' => array_map([$this->employeesJobTitleValidationService, 'formatEmployeeJobTitleTranslationData'], $translations),
            'message' => "EmployeeJobTitle with ID $id retrieved successfully."
        ];
    }

    /**
     * Создание новой Должности.
     *
     * @param array $data
     * @return array
     */
    public function createEmployeeJobTitle(array $data): array
    {
        $this->logger->info("Executing createEmployeeJobTitle method.");
        try {
            // Преобразование кода в верхний регистр непосредственно перед присвоением
            if (isset($data['EmployeeJobTitleCode'])) {
                $data['EmployeeJobTitleCode'] = strtoupper($data['EmployeeJobTitleCode']);
                $this->logger->info("EmployeeJobTitleCode after strtoupper: " . $data['EmployeeJobTitleCode']);
            }
            // Валидация данных для должности
            $this->employeesJobTitleValidationService->validateEmployeeJobTitleCode($data);
            $this->employeesJobTitleValidationService->ensureUniqueEmployeeJobTitleCode($data['EmployeeJobTitleCode'] ?? null);

            // Создаем новую должность
            $employeeJobTitle = new EmployeesJobTitle();
            $this->helper->validateAndFilterFields($employeeJobTitle, $data);//проверяем список разрешенных полей
            $employeeJobTitle->setEmployeeJobTitleCode($data['EmployeeJobTitleCode']);

            // Сохраняем должность в репозитории
            $this->employeesJobTitleRepository->saveEmployeeJobTitle($employeeJobTitle, true);
            $this->logger->info("EmployeeJobTitle '{$employeeJobTitle->getEmployeeJobTitleCode()}' created successfully.");

            // Форматируем ответ
            return [
                'employeeJobTitle' => $this->employeesJobTitleValidationService->formatEmployeesJobTitleData($employeeJobTitle),
                'message' => 'EmployeeJobTitle added successfully.'
            ];
        } catch (\InvalidArgumentException $e) {
            // Логируем ошибку валидации, чтобы избежать дублирования в контроллере
            $this->logger->error("Validation failed for creating EmployeeJobTitle: " . $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            // Логируем общую ошибку и выбрасываем исключение
            $this->logger->error("An unexpected error occurred while creating EmployeeJobTitle: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Добавление перевода для существующей должности.
     *
     * @param int $employeeJobTitleId
     * @param array $data
     * @return array
     * @throws \InvalidArgumentException Если не удалось найти должность или язык
     * @throws \Exception Если произошла непредвиденная ошибка
     */
    public function createEmployeeJobTitleTranslation(int $employeeJobTitleId, array $data): array
    {
        $this->logger->info("Executing createEmployeeJobTitleTranslation method for EmployeeJobTitle ID: $employeeJobTitleId.");
        try {
            // Проверяем существование должности
            $employeeJobTitle = $this->employeesJobTitleValidationService->validateEmployeeJobTitleExists($employeeJobTitleId);
            // Проверяем наличие выполняем валидацию
            $this->employeesJobTitleValidationService->validateEmployeeJobTitleTranslationData($data);
            // Проверяем обязательность поля EmployeeJobTitleName
            $this->employeesJobTitleValidationService->ensureUniqueEmployeeJobTitleName($data['EmployeeJobTitleName'] ?? null);
            // Проверка на наличие LanguageID и указать, что это обязательный параметр
            $languageData = $this->languagesProxyService->validateLanguageID($data['LanguageID']  ?? null);

            // Проверка уникальности перевода
            $this->employeesJobTitleValidationService->ensureUniqueTranslation($employeeJobTitle, $languageData['LanguageID']);

            // Создание нового перевода
            $translation = new EmployeeJobTitleTranslations();
            $this->helper->validateAndFilterFields($translation, $data);//проверяем список разрешенных полей
            $translation->setEmployeeJobTitleID($employeeJobTitle);
            $translation->setLanguageID($languageData['LanguageID']);

            // Применение дополнительных полей
            foreach ($data as $field => $value) {
                $setter = 'set' . ucfirst($field);
                if (method_exists($translation, $setter) && !in_array($field, ['EmployeeJobTitleID', 'LanguageID'])) {
                    $translation->$setter($value);
                } elseif (!in_array($field, ['EmployeeJobTitleID', 'LanguageID'])) {
                    throw new \InvalidArgumentException("Field '$field' does not exist on EmployeeJobTitleTranslation entity.");
                }
            }
            $this->helper->validateAndFilterFields($translation, $data);//проверяем список разрешенных полей
            // Сохранение перевода
            $this->translationRepository->saveEmployeeJobTitleTranslations($translation, true);
            $this->logger->info("Translation for EmployeeJobTitle ID $employeeJobTitleId created successfully.");

            return [
                'employeeJobTitle' => $this->employeesJobTitleValidationService->formatEmployeesJobTitleData($employeeJobTitle),
                'translation' => $this->employeesJobTitleValidationService->formatEmployeeJobTitleTranslationData($translation),
                'message' => 'EmployeeJobTitle translation added successfully.'
            ];

        } catch (\InvalidArgumentException $e) {
            // Логируем и передаем исключение с детализированным сообщением
            $this->logger->error("Validation failed for EmployeeJobTitle ID $employeeJobTitleId: " . $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            // Обработка неожиданных ошибок
            $this->logger->error("An unexpected error occurred while creating translation for EmployeeJobTitle ID $employeeJobTitleId: " . $e->getMessage());
            throw new \RuntimeException("Unable to add EmployeeJobTitle translation", 0, $e);
        }
    }

    public function updateEmployeeJobTitleCode(int $employeeJobTitleId, array $data): array
    {
        $this->logger->info("Executing updateEmployeeJobTitleCode method for EmployeeJobTitle ID: $employeeJobTitleId");

        try {
            // Получаем должность по ID и проверяем ее существование
            $employeeJobTitle = $this->employeesJobTitleValidationService->validateEmployeeJobTitleExists($employeeJobTitleId);
            if (!$employeeJobTitle) {
                $this->logger->warning("EmployeeJobTitle with ID $employeeJobTitleId not found for updating.");
                throw new \InvalidArgumentException("EmployeeJobTitle with ID $employeeJobTitleId not found.");
            }
            // Преобразование кода в верхний регистр непосредственно перед присвоением
            if (isset($data['EmployeeJobTitleCode'])) {
                $data['EmployeeJobTitleCode'] = strtoupper($data['EmployeeJobTitleCode']);
                $this->logger->info("EmployeeJobTitleCode after strtoupper: " . $data['EmployeeJobTitleCode']);
            }

            // Проверка наличия обязательных полей и их значений (в данных или в объекте)
            $requiredFields = ['EmployeeJobTitleCode'];
            foreach ($requiredFields as $field) {
                $value = $data[$field] ?? $employeeJobTitle->{'get' . $field}();
                if (empty($value)) {
                    throw new \InvalidArgumentException("Field '$field' is required and cannot be empty.");
                }
            }

            // Используем FieldUpdateHelper для обновления поля EmployeeJobTitleCode с проверками
            FieldUpdateHelper::updateFieldIfPresent(
                $employeeJobTitle,
                $data,
                'EmployeeJobTitleCode',
                function ($newCode) use ($employeeJobTitleId) {
                    $this->employeesJobTitleValidationService->ensureUniqueEmployeeJobTitleCode($newCode, $employeeJobTitleId);
                    $this->employeesJobTitleValidationService->validateEmployeeJobTitleCode(['EmployeeJobTitleCode' => $newCode]);
                }
            );

            $this->helper->validateAndFilterFields($employeeJobTitle, $data);//проверяем список разрешенных полей
            $this->employeesJobTitleRepository->saveEmployeeJobTitle($employeeJobTitle, true);

            $this->logger->info("EmployeeJobTitle Code updated successfully for EmployeeJobTitle ID: $employeeJobTitleId");

            return [
                'employeeJobTitle' => $this->employeesJobTitleValidationService->formatEmployeesJobTitleData($employeeJobTitle),
                'message' => 'EmployeeJobTitle Code updated successfully.'
            ];
        } catch (\InvalidArgumentException $e) {
            $this->logger->error("Validation failed for EmployeeJobTitle ID $employeeJobTitleId: " . $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error("An unexpected error occurred while updating EmployeeJobTitle Code for ID $employeeJobTitleId: " . $e->getMessage());
            throw new \RuntimeException("Unable to update EmployeeJobTitle Code", 0, $e);
        }
    }


    //Обновление переводов у должности
    public function updateEmployeeJobTitleTranslation(int $employeeJobTitleId, int $translationId, array $data): array
    {
        $this->logger->info("Updating translation for EmployeeJobTitle ID: $employeeJobTitleId and Translation ID: $translationId");

        try {
            $employeeJobTitle = $this->employeesJobTitleValidationService->validateEmployeeJobTitleExists($employeeJobTitleId);

            // Поиск перевода по ID
            $translation = $this->translationRepository->find($translationId);
            if (!$translation || $translation->getEmployeeJobTitleID()->getEmployeeJobTitleID() !== $employeeJobTitleId) {
                $this->logger->error("Translation for EmployeeJobTitle ID $employeeJobTitleId and Translation ID $translationId not found.");
                throw new \InvalidArgumentException("Translation with this ID does not exist for this EmployeeJobTitle.");
            }

            // Проверка, что LanguageID не был передан в запросе
            $this->languagesProxyService->checkImmutableLanguageID($data, $translation->getLanguageID());

            // Проверка наличия обязательных полей и их значений (в данных или в объекте)
            $requiredFields = ['EmployeeJobTitleName'];
            foreach ($requiredFields as $field) {
                $value = $data[$field] ?? $translation->{'get' . $field}();
                if (empty($value)) {
                    throw new \InvalidArgumentException("Field '$field' is required and cannot be empty.");
                }
            }
            // Валидация всех данных, переданных в $data
            $this->employeesJobTitleValidationService->validateEmployeeJobTitleTranslationData($data);

            // Обновление поля EmployeeJobTitleName с проверкой уникальности и наличия
            FieldUpdateHelper::updateFieldIfPresent(
                $translation,
                $data,
                'EmployeeJobTitleName',
                function ($newName) use ($translationId) {
                    $this->employeesJobTitleValidationService->ensureUniqueEmployeeJobTitleName($newName, $translationId);
                }
            );

            // Обновление полей перевода, игнорируя LanguageID
            foreach ($data as $field => $value) {
                $setter = 'set' . ucfirst($field);
                if (method_exists($translation, $setter) && !in_array($field, ['EmployeeJobTitleID'])) {
                    $translation->$setter($value);
                } elseif (!in_array($field, ['EmployeeJobTitleID'])) {
                    throw new \InvalidArgumentException("Field '$field' does not exist on EmployeeJobTitleTranslation entity.");
                }
            }
            $this->helper->validateAndFilterFields($translation, $data);//проверяем список разрешенных полей
            $this->translationRepository->saveEmployeeJobTitleTranslations($translation, true);
            $this->logger->info("Translation updated successfully for EmployeeJobTitle ID: $employeeJobTitleId and Translation ID: $translationId");

            return [
                'employeeJobTitle' => $this->employeesJobTitleValidationService->formatEmployeesJobTitleData($employeeJobTitle),
                'translation' => $this->employeesJobTitleValidationService->formatEmployeeJobTitleTranslationData($translation),
                'message' => 'EmployeeJobTitle translation updated successfully.'
            ];
        } catch (\InvalidArgumentException $e) {
            $this->logger->error("Validation failed for EmployeeJobTitle ID $employeeJobTitleId and Translation ID $translationId: " . $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error("An unexpected error occurred while updating translation for EmployeeJobTitle ID $employeeJobTitleId and Translation ID $translationId: " . $e->getMessage());
            throw new \RuntimeException("Unable to update EmployeeJobTitle translation", 0, $e);
        }
    }

    /**
     * Удаление перевода должности.
     *
     * @param int $employeeJobTitleId
     * @param int $translationId
     * @return array
     */
    public function deleteEmployeeJobTitleTranslation(int $employeeJobTitleId, int $translationId): array
    {
        $this->logger->info("Deleting translation with ID: $translationId for EmployeeJobTitle ID: $employeeJobTitleId");

        try {
            // Проверка существования должности
            $this->employeesJobTitleValidationService->validateEmployeeJobTitleExists($employeeJobTitleId);

            // Проверка существования перевода для данной должности
            $translation = $this->translationRepository->find($translationId);
            if (!$translation || $translation->getEmployeeJobTitleID()->getEmployeeJobTitleID() !== $employeeJobTitleId) {
                $this->logger->error("Translation with ID $translationId does not exist for EmployeeJobTitle ID $employeeJobTitleId.");
                throw new \InvalidArgumentException("Translation with this ID does not exist for this EmployeeJobTitle.");
            }

            // Удаление перевода
            $this->translationRepository->deleteEmployeeJobTitleTranslations($translation, true);
            $this->logger->info("Translation with ID $translationId successfully deleted for EmployeeJobTitle ID $employeeJobTitleId.");

            return [
                'message' => "Translation with ID $translationId successfully deleted for EmployeeJobTitle ID $employeeJobTitleId.",
            ];
        } catch (\InvalidArgumentException $e) {
            $this->logger->warning($e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error("An unexpected error occurred while deleting translation with ID $translationId for EmployeeJobTitle ID $employeeJobTitleId: " . $e->getMessage());
            throw new \RuntimeException("Unable to delete EmployeeJobTitle translation", 0, $e);
        }
    }


    /**
     * Удаление должности.
     *
     * @param int $employeeJobTitleId
     * @return array
     */
    public function deleteEmployeeJobTitle(int $employeeJobTitleId): array
    {
        try {
            $this->logger->info("Executing deleteEmployeeJobTitle method for ID: $employeeJobTitleId");

            $employeeJobTitle = $this->employeesJobTitleValidationService->validateEmployeeJobTitleExists($employeeJobTitleId);


            // Удаляем переводы должности
            $translations = $this->translationRepository->findTranslationsByEmployeesJobTitle($employeeJobTitle);
            foreach ($translations as $translation) {
                $this->translationRepository->deleteEmployeeJobTitleTranslations($translation, true);
            }

            // Удаляем саму должность
            $this->employeesJobTitleRepository->deleteEmployeeJobTitle($employeeJobTitle, true);
            $this->logger->info("EmployeeJobTitle with ID $employeeJobTitleId and its translations successfully deleted.");

            return [
                'message' => "EmployeeJobTitle with ID $employeeJobTitleId and its translations successfully deleted.",
            ];
        } catch (\InvalidArgumentException $e) {
            $this->logger->warning($e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error("An unexpected error occurred while deleting EmployeeJobTitle with ID $employeeJobTitleId: " . $e->getMessage());
            throw $e;
        }
    }

    public function seedJobTitlesAndTranslations(): array
    {
        $this->logger->info("Executing seedJobTitlesAndTranslations method.");

        // Данные для предустановленных должностей
        $jobTitlesData = [
            ["EmployeeJobTitleCode" => "HIRED"],
            ["EmployeeJobTitleCode" => "FIRED"],
            //["EmployeeJobTitleCode" => "SINGUP"],
        ];

        $createdJobTitles = [];
        $jobTitleIds = [];

        // Создаём должности и сохраняем их ID
        foreach ($jobTitlesData as $jobTitleData) {
            try {
                $this->employeesJobTitleValidationService->validateEmployeeJobTitleCode($jobTitleData);
                $this->employeesJobTitleValidationService->ensureUniqueEmployeeJobTitleCode($jobTitleData['EmployeeJobTitleCode']);

                $jobTitle = new EmployeesJobTitle();
                $jobTitle->setEmployeeJobTitleCode($jobTitleData['EmployeeJobTitleCode']);
                $this->employeesJobTitleRepository->saveEmployeeJobTitle($jobTitle, true);

                $createdJobTitles[] = $this->employeesJobTitleValidationService->formatEmployeesJobTitleData($jobTitle);
                $jobTitleIds[$jobTitleData['EmployeeJobTitleCode']] = $jobTitle->getEmployeeJobTitleID(); // Сохраняем ID должности

                $this->logger->info("Job title '{$jobTitleData['EmployeeJobTitleCode']}' created successfully.");
            } catch (\InvalidArgumentException $e) {
                $this->logger->warning("Job title '{$jobTitleData['EmployeeJobTitleCode']}' already exists or is invalid. Skipping.");
            }
        }

        // Данные для переводов должностей, привязанные к EmployeeJobTitleID
        $translationsData = [
                $jobTitleIds['HIRED'] ?? null => [
                ["EmployeeJobTitleName" => "Новый сотрудник", "LanguageID" => 2],
                ["EmployeeJobTitleName" => "New employee", "LanguageID" => 1]
            ],
                $jobTitleIds['FIRED'] ?? null => [
                ["EmployeeJobTitleName" => "Сотрудник уволен", "LanguageID" => 2],
                ["EmployeeJobTitleName" => "Employee dismissed", "LanguageID" => 1]
            ],
//                $jobTitleIds['SINGUP'] ?? null => [
//                ["EmployeeJobTitleName" => "Сотрудник зарегистрирован", "LanguageID" => 2],
//                ["EmployeeJobTitleName" => "The employee is registered", "LanguageID" => 1]
//            ],
        ];

        $createdTranslations = [];

        // Создаём переводы для каждой должности, используя их ID
        foreach ($translationsData as $jobTitleId => $translations) {
            if (!$jobTitleId) {
                continue; // Пропускаем, если ID не найден
            }

            $jobTitle = $this->employeesJobTitleRepository->findEmployeeJobTitleById($jobTitleId);

            foreach ($translations as $translationData) {
                try {
                    $languageData = $this->languagesProxyService->validateLanguageID($translationData['LanguageID']);
                    $languageId = $languageData['LanguageID'];
                    $this->employeesJobTitleValidationService->ensureUniqueTranslation($jobTitle, $languageId);

                    $translation = new EmployeeJobTitleTranslations();
                    $translation->setEmployeeJobTitleID($jobTitle);
                    $translation->setLanguageID($languageId);
                    $translation->setEmployeeJobTitleName($translationData['EmployeeJobTitleName']);

                    $this->translationRepository->saveEmployeeJobTitleTranslations($translation, true);
                    $createdTranslations[] = $this->employeesJobTitleValidationService->formatEmployeeJobTitleTranslationData($translation);

                    $this->logger->info("Translation for JobTitle ID '{$jobTitleId}' and LanguageID '{$languageId}' created successfully.");
                } catch (\Exception $e) {
                    $this->logger->error("Failed to add translation for JobTitle ID '$jobTitleId': " . $e->getMessage());
                }
            }
        }

        return [
            'jobTitles' => $createdJobTitles,
            'translations' => $createdTranslations,
            'message' => 'Job titles and translations seeded successfully.'
        ];
    }

}
