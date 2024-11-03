<?php

namespace Module\Employees\Service;

use Module\Categories\Repository\CategoriesRepository;
use Module\Employees\Repository\EmployeesRepository;
use Module\Languages\Repository\LanguagesRepository;
use Module\Employees\Entity\Employee;
use Module\Common\Service\EmployeesValidationService;
use Module\Common\Service\CategoriesValidationService;
use Module\Common\Service\LanguagesValidationService;
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

    public function __construct(
        EmployeesRepository $employeeRepository,
        LanguagesRepository $languageRepository,
        CategoriesRepository $categoriesRepository,
        LanguagesValidationService $languagesValidationService,
        CategoriesValidationService $categoriesValidationService,
        EmployeesValidationService $employeesValidationService,
        LoggerInterface $logger)
    {
        $this->employeeRepository = $employeeRepository;
        $this->languageRepository = $languageRepository;
        $this->categoriesRepository = $categoriesRepository;
        $this->languagesValidationService = $languagesValidationService;
        $this->categoriesValidationService = $categoriesValidationService;
        $this->employeesValidationService = $employeesValidationService;
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
                    'message' => 'No employees found in the database.'
                ];
            }

            // Форматируем каждого сотрудника и добавляем ключ для структурированного ответа
            return [
                'employees' => array_map([$this->employeesValidationService, 'formatEmployeeData'], $employees)
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
        return $this->employeesValidationService->formatEmployeeData($employee, true);
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
            // Сохраняем сотрудника в репозитории
            $this->employeeRepository->saveEmployee($employee, true);
            $this->logger->info("Employee '{$employee->getEmployeeName()}' created successfully.");
            // Формируем и возвращаем ответ
            return [
                'Employee' => $this->employeesValidationService->formatEmployeeData($employee, true),
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

            // Валидация данных перед обновлением сотрудника
            $this->employeesValidationService->validateEmployeeData($data, false);

            // Обрабатываем каждый элемент в массиве данных
//            foreach ($data as $field => $value) {
//                $setter = 'set' . ucfirst($field);
//                if ($field === 'LanguageID') {
//                    $this->languagesValidationService->validateLanguageID($value); // Валидация существования языка
//                    $language = $this->languageRepository->find($value);
//                    $employee->setEmployeeLanguageID($language);
//                } elseif ($field === 'EmployeeCategoryID' && is_int($value)) {
//                    // Обрабатываем `EmployeeCategoryID` отдельно
//                    $employee->$setter($value);
//                } elseif (method_exists($employee, $setter)) {
//                    $employee->$setter($value);
//                } else {
//                    $this->logger->warning("Setter method '$setter' does not exist for field '$field'. Skipping.");
//                }
//            }

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

            // Сохраняем изменения
            $this->employeeRepository->saveEmployee($employee, true);
            $this->logger->info("Employee with ID $id successfully updated.");

            // Формируем и возвращаем отформатированные данные
            return [
                'Employee' => $this->employeesValidationService->formatEmployeeData($employee, true),
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
                'status' => true,
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
