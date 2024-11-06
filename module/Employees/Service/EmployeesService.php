<?php

namespace Module\Employees\Service;

use Module\Categories\Repository\CategoriesRepository;
use Module\Employees\Repository\EmployeesRepository;
use Module\Languages\Repository\LanguagesRepository;
use Module\Employees\Entity\Employee;
use Module\Common\Service\EmployeesValidationService;
use Module\Common\Service\CategoriesValidationService;
use Module\Common\Service\LanguagesValidationService;
use Module\Common\Helpers\FieldUpdateHelper;
use Psr\Log\LoggerInterface;

class EmployeesService
{
    private EmployeesRepository $employeeRepository;
    private LanguagesRepository $languageRepository;
    private CategoriesRepository $categoriesRepository;
    private LoggerInterface $logger;
    private LanguagesValidationService $languagesValidationService;
    private CategoriesValidationService $categoriesValidationService;
    private EmployeesValidationService $employeesValidationService;
    private FieldUpdateHelper $helper;

    public function __construct(
        EmployeesRepository $employeeRepository,
        LanguagesRepository $languageRepository,
        CategoriesRepository $categoriesRepository,
        LanguagesValidationService $languagesValidationService,
        CategoriesValidationService $categoriesValidationService,
        EmployeesValidationService $employeesValidationService,
        FieldUpdateHelper $helper,
        LoggerInterface $logger)
    {
        $this->employeeRepository = $employeeRepository;
        $this->languageRepository = $languageRepository;
        $this->categoriesRepository = $categoriesRepository;
        $this->languagesValidationService = $languagesValidationService;
        $this->categoriesValidationService = $categoriesValidationService;
        $this->employeesValidationService = $employeesValidationService;
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
            //Валидация на проверку языка и категории
            $this->employeesValidationService->ensureUniqueEmployeeName($data['EmployeeName'] ?? null); // Проверка обязателен ли EmployeeName
            $this->employeesValidationService->ensureUniqueEmployeeJobTitle($data['EmployeeJobTitle'] ?? null); // Проверка обязателен ли EmployeeJobTitle

            $language = $this->languagesValidationService->validateLanguageID($data['LanguageID'] ?? null); // Проверка существования языка
            $category = $this->categoriesValidationService->validateCategoryExists($data['CategoryID'] ?? null); // Проверка существования категории

            $employee = new Employee();
            $employee->setEmployeeLanguageID($language);
            $employee->setEmployeeCategoryID($category);

            // Устанавливаем значения полей
            foreach ($data as $field => $value) {
                $setter = 'set' . ucfirst($field);

                if (!in_array($field, ['LanguageID', 'CategoryID']) && method_exists($employee, $setter)) {
                    $employee->$setter($value);
                } elseif (!in_array($field, ['LanguageID', 'CategoryID'])) {
                    throw new \InvalidArgumentException("Field '$field' does not exist on Employee entity.");
                }
            }
            $this->helper->validateAndFilterFields($employee, array_diff_key($data, array_flip(['LanguageID', 'CategoryID'])));
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

            // Обновление EmployeeLink
            FieldUpdateHelper::updateFieldIfPresent(
                $employee,
                $data,
                'EmployeeLink',
                fn($value) => $this->employeesValidationService->ensureUniqueEmployeeLink($value, $id)
            );

            // Обновление EmployeeName
            FieldUpdateHelper::updateFieldIfPresent(
                $employee,
                $data,
                'EmployeeName',
                fn($value) => $this->employeesValidationService->ensureUniqueEmployeeName($value, $id)
            );

            // Обновление EmployeeJobTitle
            FieldUpdateHelper::updateFieldIfPresent(
                $employee,
                $data,
                'EmployeeJobTitle',
                fn($value) => $this->employeesValidationService->ensureUniqueEmployeeJobTitle($value, $id)
            );

            // Обновление полей сотрудника
            foreach ($data as $field => $value) {
                $setter = 'set' . ucfirst($field);

                // Обработка LanguageID
                if ($field === 'LanguageID') {
                    $language = $this->languagesValidationService->validateLanguageID($value);
                    $employee->setEmployeeLanguageID($language);
                }
                // Обработка CategoryID
                elseif ($field === 'CategoryID') {
                    $category = $this->categoriesValidationService->validateCategoryExists($value);
                    $employee->setEmployeeCategoryID($category);
                }
                // Обработка остальных полей
                elseif (method_exists($employee, $setter)) {
                    $employee->$setter($value);
                } else {
                    $this->logger->warning("Field '$field' does not exist on Employee entity. Skipping.");
                }
            }

            $this->helper->validateAndFilterFields($employee, array_diff_key($data, array_flip(['LanguageID', 'CategoryID'])));
            // Сохраняем изменения
            $this->employeeRepository->saveEmployee($employee, true);
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

}
