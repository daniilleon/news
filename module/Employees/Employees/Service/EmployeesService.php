<?php

namespace Module\Employees\Employees\Service;

use InvalidArgumentException;
use Module\Common\Helpers\FieldUpdateHelper;
use Module\Employees\Employees\Entity\Employee;
use Module\Employees\Employees\Repository\EmployeesRepository;
use \Module\Employees\Employees\Service\EmployeesValidationService;
use Module\Employees\EmployeesJobTitle\Repository\EmployeesJobTitleRepository;
use Module\Employees\EmployeesJobTitle\Service\EmployeesJobTitleValidationService;
use Module\Common\Service\LanguagesProxyService;
use Module\Common\Service\CategoriesProxyService;
use Psr\Log\LoggerInterface;

class EmployeesService
{
    private EmployeesRepository $employeeRepository;
    private EmployeesJobTitleRepository $employeesJobTitleRepository;
    private EmployeesValidationService $employeesValidationService;
    private EmployeesJobTitleValidationService $employeesJobTitleValidationService;
    private LanguagesProxyService $languagesProxyService;
    private CategoriesProxyService $categoriesProxyService;
    private LoggerInterface $logger;
    private FieldUpdateHelper $helper;

    public function __construct(
        EmployeesRepository $employeeRepository,
        EmployeesJobTitleRepository $employeesJobTitleRepository,
        LanguagesProxyService $languagesProxyService,
        CategoriesProxyService $categoriesProxyService,
        EmployeesValidationService $employeesValidationService,
        EmployeesJobTitleValidationService $employeesJobTitleValidationService,
        FieldUpdateHelper $helper,
        LoggerInterface $logger)
    {
        $this->employeeRepository = $employeeRepository;
        $this->employeesJobTitleRepository = $employeesJobTitleRepository;
        $this->languagesProxyService = $languagesProxyService;
        $this->categoriesProxyService = $categoriesProxyService;
        $this->employeesValidationService = $employeesValidationService;
        $this->employeesJobTitleValidationService = $employeesJobTitleValidationService;
        $this->helper = $helper;
        $this->logger = $logger;
        $this->logger->info("EmployeeService instance created.");
    }

    /**
     * Получение всех сотрудников.
     * @return array
     */
    public function getAllEmployees(): array
    {
        try {
            $this->logger->info("Executing getAllEmployees method.");
            $employees = $this->employeeRepository->findAllEmployees();

            if (empty($employees)) {
                $this->logger->info("No employees found in the database.");
                return [
                    'employees' => [],  // Возвращаем пустой массив вместо сообщения
                    'message' => 'No employees found in the database.'
                ];
            }

            // Форматируем каждого сотрудника и добавляем ключ для структурированного ответа
            return [
                'employees' => array_map([$this->employeesValidationService, 'formatEmployeeData'], $employees),
                'message' => 'Employees retrieved successfully.'
            ];
        } catch (\InvalidArgumentException $e) {
            $this->logger->error("Validation error while fetching employees: " . $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error("Unable to fetch employees: " . $e->getMessage());
            throw new \RuntimeException("Unable to fetch employees at the moment.", 0, $e);
        }
    }

    /**
     * Получение данных сотрудника по ID.
     *
     * @param int $id
     * @return array|null
     */
    public function getEmployeeById(int $id): ?array
    {
        $this->logger->info("Executing getEmployeeById method for ID: $id");

        // Проверка существования сотрудника или выброс исключения
        $employee = $this->employeeRepository->findEmployeeById($id);
        if (!$employee) {
            $this->logger->warning("Employee with ID $id not found.");
            throw new \InvalidArgumentException("Employee with ID $id not found.");
        }

        // Форматирование данных сотрудника для вывода
        return [
            'employee' => $this->employeesValidationService->formatEmployeeData($employee, true),
            'message' => "Employee with ID $id retrieved successfully."
        ];
    }

    /**
     * Создание нового сотрудника.
     *
     * @param array $data
     * @return array
     */
    public function createEmployee(array $data): array
    {
        $this->logger->info("Executing createEmployee method.");
        try {
            // Валидация данных для сотрудника
            $this->employeesValidationService->validateEmployeeData($data);
            // Проверка наличия EmployeeLink или его установки по умолчанию
            $employeeLink = $data['EmployeeLink'] ?? null;  // Устанавливаем null, если ключ отсутствует
            $this->employeesValidationService->ensureUniqueEmployeeLink($employeeLink);
            //Валидация на проверку имени
            $this->employeesValidationService->ensureUniqueEmployeeName($data['EmployeeName'] ?? null); // Проверка обязателен ли EmployeeName

            //$employeeJobTitle = $this->employeesJobTitleValidationService->validateEmployeeJobTitleExists($data['EmployeeJobTitleID'] ?? null); // Проверка обязателен ли EmployeeJobTitle
            // Валидация языка и категории
            $languageData = $this->languagesProxyService->validateLanguageID($data['LanguageID'] ?? null); // Проверка существования языка
            $categoryData = $this->categoriesProxyService->validateCategoryExists($data['CategoryID'] ?? null); // Проверка существования категории

            // Поиск должности с кодом HIRED
            $employeeJobTitle = $this->employeesJobTitleRepository->findEmployeeJobTitleByCode('HIRED');
            if (!$employeeJobTitle) {
                $this->logger->error("Job title with code 'HIRED' not found.");
                throw new \InvalidArgumentException("Default job title 'HIRED' is missing.");
            }

            $employee = new Employee();
            $employee->setEmployeeJobTitleID($employeeJobTitle);
            $employee->setLanguageID($languageData['LanguageID']);
            $employee->setCategoryID($categoryData['CategoryID']);

            // Устанавливаем значения полей
            foreach ($data as $field => $value) {
                $setter = 'set' . ucfirst($field);

                if (!in_array($field, ['LanguageID', 'CategoryID', 'EmployeeJobTitleID', 'EmployeeActive']) && method_exists($employee, $setter)) {
                    $employee->$setter($value);
                } elseif (!in_array($field, ['LanguageID', 'CategoryID'])) {
                    throw new \InvalidArgumentException("Field '$field' does not exist on Employee entity.");
                }
            }
            // Исключение из массива данных перед фильтрацией и валидацией
            $this->helper->validateAndFilterFields($employee, array_diff_key($data, array_flip(['LanguageID', 'CategoryID', 'EmployeeJobTitleID', 'EmployeeActive'])));
            // Сохраняем сотрудника в репозитории
            $this->employeeRepository->saveEmployee($employee, true);
            $this->logger->info("Employee '{$employee->getEmployeeName()}' created successfully.");
            // Формируем и возвращаем ответ
            return [
                'employee' => $this->employeesValidationService->formatEmployeeData($employee, true),
                'message' => 'Employee added successfully.'
            ];
        } catch (\InvalidArgumentException $e) {
            $this->logger->error("Validation failed for creating employee: " . $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error("An unexpected error occurred while creating employee: " . $e->getMessage());
            throw $e;
        }
    }


    /**
     * Обновление данных сотрудника.
     *
     * @param int $id
     * @param array $data
     * @return array
     */
    public function updateEmployee(int $id, array $data): array
    {
        $this->logger->info("Executing updateEmployee method for ID: $id");

        try {
            // Получаем сотрудника из базы данных
            $employee = $this->employeeRepository->findEmployeeById($id);
            if (!$employee) {
                $this->logger->warning("Employee with ID $id not found for updating.");
                throw new \InvalidArgumentException("Employee with ID $id not found.");
            }

            // Проверка наличия обязательных полей и их значений (в данных или в объекте)
            $requiredFields = ['EmployeeLink', 'EmployeeName'];
            foreach ($requiredFields as $field) {
                $value = $data[$field] ?? $employee->{'get' . $field}();
                if (empty($value)) {
                    throw new \InvalidArgumentException("Field '$field' is required and cannot be empty.");
                }
            }

            // Используем FieldUpdateHelper для обновления обязательных полей EmployeeLink и EmployeeName с проверками
            FieldUpdateHelper::updateFieldIfPresent(
                $employee,
                $data,
                'EmployeeLink',
                function ($newLink) use ($id) {
                    $this->employeesValidationService->ensureUniqueEmployeeLink($newLink, $id);
                    $this->employeesValidationService->validateEmployeeData(['EmployeeLink' => $newLink]);

                }
            );

            FieldUpdateHelper::updateFieldIfPresent(
                $employee,
                $data,
                'EmployeeName',
                function ($newName) use ($id) {
                    $this->employeesValidationService->ensureUniqueEmployeeName($newName, $id);
                    $this->employeesValidationService->validateEmployeeData(['EmployeeName' => $newName]);
                }
            );

            $this->logger->info("Data before filtering: ", $data);
            // Обновление полей сотрудника
            foreach ($data as $field => $value) {
                $setter = 'set' . ucfirst($field);

                // Обработка LanguageID
                if ($field === 'LanguageID') {
                    $languageData = $this->languagesProxyService->validateLanguageID($value);
                    $employee->setLanguageID($languageData['LanguageID']);
                }
                // Обработка CategoryID
                elseif ($field === 'CategoryID') {
                    $categoryData = $this->categoriesProxyService->validateCategoryExists($value);
                    $this->logger->info("Updating CategoryID to: " . $categoryData['CategoryID']);
                    $employee->setCategoryID($categoryData['CategoryID']);
                    $this->logger->info("CategoryID after update: " . $employee->getCategoryID());
                }
                // Обработка EmployeeJobTitleID
                elseif ($field === 'EmployeeJobTitleID') {
                    $employeeJobTitle = $this->employeesJobTitleValidationService->validateEmployeeJobTitleExists($value);

                    // Проверяем, чтобы должность не была "FIRED"
                    if ($employeeJobTitle->getEmployeeJobTitleCode() === 'FIRED') {
                        throw new \InvalidArgumentException("The job title 'FIRED' cannot be manually assigned.");
                    }

                    $employee->setEmployeeJobTitleID($employeeJobTitle);
                }
                if (!in_array($field, ['LanguageID', 'CategoryID', 'EmployeeJobTitleID', 'EmployeeActive']) && method_exists($employee, $setter)) {
                    $employee->$setter($value);
                } elseif (!in_array($field, ['LanguageID', 'CategoryID', 'EmployeeJobTitleID'])) {
                    throw new \InvalidArgumentException("Field '$field' does not exist on Employee entity.");
                }
            }

            // Исключение из массива данных перед фильтрацией и валидацией
            //$this->helper->validateAndFilterFields($employee, array_diff_key($data, array_flip(['LanguageID', 'CategoryID', 'EmployeeActive'])));

            $this->logger->info("Saving Employee: " . json_encode([
                    'ID' => $employee->getEmployeeID(),
                    'CategoryID' => $employee->getCategoryID(),
                ]));
            // Сохраняем изменения
            $this->employeeRepository->saveEmployee($employee, true);
            $this->logger->info("Changes flushed for Employee ID: " . $employee->getEmployeeID());
            $this->logger->info("Employee with ID $id successfully updated.");

            // Формируем и возвращаем отформатированные данные
            return [
                'employee' => $this->employeesValidationService->formatEmployeeData($employee, true),
                'message' => 'Employee updated successfully.'
            ];

        } catch (\InvalidArgumentException $e) {
            // Логируем исключение здесь, чтобы избежать дублирования в контроллере
            $this->logger->error("Validation failed for Employee ID $id: " . $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            // Логируем другие ошибки
            $this->logger->error("An unexpected error occurred while updating employee with ID $id: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Активация или деактивация сотрудника.
     *
     * @param int $id
     * @param bool $activeStatus
     * @return array
     * @throws InvalidArgumentException если сотрудник не найден
     */
    public function toggleEmployeeStatus(int $id, array $data): array
    {
        $this->logger->info("Executing toggleEmployeeStatus method for ID: $id");

        try {
            // Получаем сотрудника из базы данных
            $employee = $this->employeeRepository->findEmployeeById($id);
            if (!$employee) {
                $this->logger->warning("Employee with ID $id not found for status update.");
                throw new \InvalidArgumentException("Employee with ID $id not found.");
            }

            // Проверка на наличие только допустимого поля EmployeeActive
            foreach ($data as $field => $value) {
                if ($field !== 'EmployeeActive') {
                    throw new \InvalidArgumentException("Field '$field' is not allowed. Only 'EmployeeActive' can be updated.");
                }
            }

            // Устанавливаем активный статус и фильтруем поле
            $this->employeesValidationService->ensureUniqueEmployeeActive($data['EmployeeActive']  ?? null);
            $employee->setEmployeeActive($data['EmployeeActive']);

            // Обновляем должность сотрудника в зависимости от статуса
            $this->updateJobTitleBasedOnStatus($employee, $data['EmployeeActive']);

            // Сохраняем изменения
            $this->employeeRepository->saveEmployee($employee, true);
            $this->logger->info("Employee with ID $id successfully " . ($data['EmployeeActive'] ? 'activated' : 'deactivated') . ".");

            // Формируем ответ
            return [
                'employee' => $this->employeesValidationService->formatEmployeeData($employee, true),
                'message' => "Employee status updated successfully"
            ];

        } catch (\InvalidArgumentException $e) {
            // Логируем и пробрасываем исключение, если возникла ошибка валидации
            $this->logger->error("Validation failed for Employee ID $id: " . $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            // Логирование других ошибок
            $this->logger->error("An unexpected error occurred while updating status for employee with ID $id: " . $e->getMessage());
            throw $e;
        }
    }


    /**
     * Удаление сотрудника по ID
     *
     * @param int $id
     * @return array
     */
    public function deleteEmployee(int $id): array
    {
        try {
            $this->logger->info("Executing deleteEmployee method for ID: $id");

            $employee = $this->employeeRepository->findEmployeeById($id);
            if (!$employee) {
                $this->logger->warning("Employee with ID $id not found for deletion.");
                throw new \InvalidArgumentException("Employee with ID $id not found for deletion.");
            }

            $this->employeeRepository->deleteEmployee($employee, true);
            $this->logger->info("Employee with ID $id successfully deleted.");

            return [
                'message' => "Employee with ID $id successfully deleted.",
            ];
        } catch (\InvalidArgumentException $e) {
            $this->logger->warning($e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error("An unexpected error occurred while deleting employee with ID $id: " . $e->getMessage());
            throw new \RuntimeException("Unable to delete employee", 0, $e);
        }
    }

    /**
     * Обновляет должность сотрудника в зависимости от статуса активности.
     *
     * @param Employee $employee
     * @param bool $isActive
     */
    private function updateJobTitleBasedOnStatus(Employee $employee, bool $isActive): void
    {
        $jobTitleCode = $isActive ? 'HIRED' : 'FIRED';

        try {
            // Пытаемся найти должность по коду
            $jobTitle = $this->employeesJobTitleRepository->findEmployeeJobTitleByCode($jobTitleCode);

            // Если должность найдена, назначаем её сотруднику
            if ($jobTitle) {
                $employee->setEmployeeJobTitleID($jobTitle);
                $this->logger->info("Employee with ID {$employee->getEmployeeID()} job title set to '$jobTitleCode' based on active status.");
            } else {
                $this->logger->warning("Job title with code '$jobTitleCode' not found.");
                throw new \InvalidArgumentException("Job title with code '$jobTitleCode' not found for employee with ID {$employee->getEmployeeID()}.");
            }
        } catch (\InvalidArgumentException $e) {
            $this->logger->warning($e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            // Логируем исключение и пробрасываем его дальше
            $this->logger->error("Failed to update job title for employee with ID {$employee->getEmployeeID()}: " . $e->getMessage());
            throw new \RuntimeException("Unable to update job title based on employee status.", 0, $e);
        }
    }


    //Простое добавление сотрудника демо версией
    public function seedEmployees(): array
    {
        $this->logger->info("Executing seedEmployees method.");

        $employees = [
            [
                "EmployeeLink" => "demo_1",
                "EmployeeName" => "Пробный сотрудник",
                "LanguageID" => 2,
                "CategoryID" => 1
            ],
            [
                "EmployeeLink" => "demo_2",
                "EmployeeName" => "Demo staff",
                "LanguageID" => 1,
                "CategoryID" => 2
            ]
        ];

        $addedEmployees = [];

        foreach ($employees as $employeeData) {
            $employeeLink = $employeeData['EmployeeLink'];
            $employeeName = $employeeData['EmployeeName'];
            $languageID = $employeeData['LanguageID'];
            $categoryID = $employeeData['CategoryID'];

            // Проверка, существует ли сотрудник с таким EmployeeLink
            if ($this->employeeRepository->findEmployeeByLink($employeeLink)) {
                $this->logger->info("Employee with link '$employeeLink' already exists. Skipping.");
                continue;
            }

            try {
                // Проверка языка и категории
                $languageData = $this->languagesProxyService->validateLanguageID($languageID);
                $categoryData = $this->categoriesProxyService->validateCategoryExists($categoryID);

                // Поиск должности с кодом HIRED
                $employeeJobTitle = $this->employeesJobTitleRepository->findEmployeeJobTitleByCode('HIRED');
                if (!$employeeJobTitle) {
                    $this->logger->error("Job title with code 'HIRED' not found. Skipping employee '$employeeName'.");
                    continue;
                }

                // Создание нового сотрудника
                $employee = new Employee();
                $employee->setEmployeeLink($employeeLink)
                    ->setEmployeeName($employeeName)
                    ->setLanguageID($languageData['LanguageID'])
                    ->setCategoryID($categoryData['CategoryID'])
                    ->setEmployeeJobTitleID($employeeJobTitle)
                    ->setEmployeeActive(true); // По умолчанию активен

                // Сохранение сотрудника
                $this->employeeRepository->saveEmployee($employee, true);

                $this->logger->info("Employee '$employeeName' with link '$employeeLink' successfully added.");
                $addedEmployees[] = [
                    'message' => 'Employee added successfully.',
                    'employee' => $this->employeesValidationService->formatEmployeeData($employee, true)
                ];
            } catch (\Exception $e) {
                $this->logger->error("Failed to add employee '$employeeName': " . $e->getMessage());
            }
        }

        return $addedEmployees;
    }

}
