<?php

namespace Module\Employees\Service;

use Module\Employees\Repository\EmployeesRepository;
use Module\Languages\Repository\LanguagesRepository;
use Module\Employees\Entity\Employee;
use Module\Languages\Entity\Language;
use Module\Common\Service\LanguagesValidationService;
use Psr\Log\LoggerInterface;

class EmployeesService
{
    private EmployeesRepository $employeeRepository;
    private LanguagesRepository $languageRepository;
    private LoggerInterface $logger;
    private LanguagesValidationService $validationService;

    public function __construct(
        EmployeesRepository $employeeRepository, LanguagesRepository $languageRepository,
        LanguagesValidationService $validationService, LoggerInterface $logger)
    {
        $this->employeeRepository = $employeeRepository;
        $this->languageRepository = $languageRepository;
        $this->validationService = $validationService;
        $this->logger = $logger;
        $this->logger->info("EmployeeService instance created.");
    }


    /**
     * Валидация данных сотрудника.
     *
     * @param array $data
     * @return void
     */
    private function validateEmployeeData(array $data, bool $isNew = true): void
    {
        // Проверка обязательных полей при создании нового сотрудника
        if ($isNew) {
            if (empty($data['EmployeeName'])) {
                throw new \InvalidArgumentException("Field 'EmployeeName' is required.");
            }
            if (empty($data['EmployeeJobTitle'])) {
                throw new \InvalidArgumentException("Field 'EmployeeJobTitle' is required.");
            }
            if (empty($data['EmployeeLink'])) {
                throw new \InvalidArgumentException("Field 'EmployeeLink' is required.");
            }
            if (empty($data['EmployeeCategoryID'])) {
                throw new \InvalidArgumentException("Field 'EmployeeCategoryID' is required.");
            }
            if (empty($data['LanguageID'])) {
                throw new \InvalidArgumentException("Field 'LanguageID' is required.");
            }
        }

        // Валидация URL и текстовых полей сотрудника
        if (!empty($data['EmployeeLink']) && !preg_match('/^[a-zA-Z0-9_-]+$/', $data['EmployeeLink'])) {
            throw new \InvalidArgumentException("Field 'EmployeeLink' can contain only letters, numbers, underscores, and hyphens.");
        }

        // Изменено регулярное выражение для поддержки всех алфавитов
        if (!empty($data['EmployeeName']) && !preg_match('/^[\p{L}\s]+$/u', $data['EmployeeName'])) {
            throw new \InvalidArgumentException("Field 'EmployeeName' can contain only letters and spaces.");
        }

        if (!empty($data['EmployeeJobTitle']) && !preg_match('/^[a-zA-Z\s0-9]+$/', $data['EmployeeJobTitle'])) {
            throw new \InvalidArgumentException("Field 'EmployeeJobTitle' can contain only letters, spaces, and numbers.");
        }

        if (!empty($data['EmployeeDescription']) && !preg_match('/^[\p{L}0-9\s.,\/\\\\]+$/u', $data['EmployeeDescription'])) {
            throw new \InvalidArgumentException("Field 'EmployeeDescription' can contain only letters, spaces, numbers, dots, commas, and / \\ characters.");
        }

        // Валидация полей социальных сетей
        foreach (['EmployeeLinkedIn', 'EmployeeInstagram', 'EmployeeFacebook', 'EmployeeTwitter'] as $field) {
            if (!empty($data[$field]) && !preg_match('/^[a-zA-Z0-9@._-]*$/', $data[$field])) {
                throw new \InvalidArgumentException("Field '{$field}' can contain only letters, numbers, @, ., _ and -.");
            }
        }

        // Проверка целочисленного значения для ID категории, если оно указано
        if (isset($data['EmployeeCategoryID']) && !is_int($data['EmployeeCategoryID'])) {
            throw new \InvalidArgumentException("Field 'EmployeeCategoryID' must be an integer.");
        }
    }



    /**
     * Получение всех сотрудников.
     *
     * @return Employee[]
     */
    public function getAllEmployees(): array
    {
        $this->logger->info("Executing getAllEmployees method.");
        $employees = $this->employeeRepository->findAllEmployees();

        if (empty($employees)) {
            $this->logger->info("No employees found in database.");
        }

        return $employees;
    }

    /**
     * Получение сотрудника по ID.
     *
     * @param int $id
     * @return Employee|null
     */
    public function getEmployeeById(int $id): ?Employee
    {
        $this->logger->info("Executing getEmployeeById method for ID: {$id}");

        $employee = $this->employeeRepository->findEmployeeById($id);

        if (!$employee) {
            $this->logger->info("Employee with ID {$id} not found.");
        }

        return $employee;
    }

    /**
     * Добавление нового сотрудника.
     *
     * @param array $data
     * @return Employee
     */
    public function addEmployee(array $data): Employee
    {
        $this->logger->info("Executing addEmployee method.");
        // Валидация данных перед добавлением сотрудника
        $this->validateEmployeeData($data);

        // Проверка на наличие `LanguageID`
        if (!isset($data['LanguageID'])) {
            $this->logger->error("LanguageID is missing in the provided data.");
            throw new \InvalidArgumentException("LanguageID is required.");
        }

        // Используем Общий ValidationService для проверки `LanguageID`
        if (!$this->validationService->validateLanguageID($data['LanguageID'])) {
            throw new \InvalidArgumentException("Language with ID {$data['LanguageID']} not found.");
        }

        // Получаем объект Language из LanguageRepository
        $language = $this->languageRepository->find($data['LanguageID']);

        $employee = new Employee();
        // Устанавливаем динамически все поля из $data
        foreach ($data as $field => $value) {
            $setter = 'set' . ucfirst($field);

            // Обрабатываем `LanguageID` отдельно, направляя его к `setEmployeeLanguageID`
            if ($field === 'LanguageID') {
                $employee->setEmployeeLanguageID($language);
            } elseif (method_exists($employee, $setter)) {
                $employee->$setter($value);
            }
        }

        // Сохраняем сотрудника в базе данных
        $this->employeeRepository->saveEmployee($employee, true);
        $this->logger->info("Employee '{$employee->getEmployeeName()}' added with ID: {$employee->getEmployeeID()}.");

        return $employee;
    }

    /**
     * Обновление данных сотрудника.
     *
     * @param int $id
     * @param array $data
     * @return Employee|null
     */
    public function updateEmployee(int $id, array $data): ?Employee
    {
        $this->logger->info("Executing updateEmployee method for ID: $id");

        try {
            // Получаем сотрудника из базы данных
            $employee = $this->employeeRepository->findEmployeeById($id);
            if (!$employee) {
                $this->logger->warning("Employee with ID $id not found for updating.");
                return null;
            }

            // Валидация данных перед обновлением сотрудника
            $this->validateEmployeeData($data, false);

            // Обрабатываем каждый элемент в массиве данных
            foreach ($data as $field => $value) {
                $setter = 'set' . ucfirst($field);

                if ($field === 'LanguageID') {
                    // Получаем язык по `LanguageID`
                    if (!$this->validationService->validateLanguageID($value)) {
                        throw new \InvalidArgumentException("Language with ID {$value} not found.");
                    }
                    $language = $this->languageRepository->find($value);
                    $employee->setEmployeeLanguageID($language);
                } elseif ($field === 'EmployeeCategoryID' && is_int($value)) {
                    // Обрабатываем `EmployeeCategoryID` отдельно
                    $employee->$setter($value);
                } elseif (method_exists($employee, $setter)) {
                    $employee->$setter($value);
                } else {
                    $this->logger->warning("Setter method '$setter' does not exist for field '$field'. Skipping.");
                }
            }

            // Сохраняем изменения
            $this->employeeRepository->saveEmployee($employee, true);
            $this->logger->info("Employee with ID $id successfully updated.");

            return $employee;

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
     * Обновление конкретного поля сотрудника.
     *
     * @param int $id
     * @param string $field
     * @param mixed $value
     * @return Employee|null
     */
    public function updateEmployeeField(int $id, string $field, $value): ?Employee
    {
        $this->logger->info("Executing updateEmployeeField method for ID: $id, Field: $field");

        try {
            $employee = $this->employeeRepository->findEmployeeById($id);
            if (!$employee) {
                $this->logger->warning("Employee with ID $id not found for field update.");
                return null;
            }

            // Проверка для обновляемого параметра `LanguageID`
            if ($field === 'LanguageID') {
                // Используем ValidationService для проверки `LanguageID`
                if (!$this->validationService->validateLanguageID($value)) {
                    throw new \InvalidArgumentException("Language with ID $value not found.");
                }
                $language = $this->languageRepository->find($value);
                $employee->setEmployeeLanguageID($language);
            } else {
                // Для других полей создаем сеттер и обновляем значение
                $setter = 'set' . ucfirst($field);
                if (method_exists($employee, $setter)) {
                    $employee->$setter($value);
                } else {
                    throw new \InvalidArgumentException("Field '$field' does not exist on Employee entity.");
                }
            }

            // Сохраняем изменения через репозиторий
            $this->employeeRepository->saveEmployee($employee, true);
            $this->logger->info("Employee with ID $id successfully updated for field $field.");

            return $employee;
        } catch (\InvalidArgumentException $e) {
            // Логируем ошибку здесь, чтобы исключить дублирование
            $this->logger->error("Validation failed for Employee ID $id: " . $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            // Логируем общую ошибку и выбрасываем исключение
            $this->logger->error("An unexpected error occurred while updating employee field for ID $id: " . $e->getMessage());
            throw $e;
        }
    }


    /**
     * Удаление сотрудника по ID.
     *
     * @param int $id
     * @return bool
     */
    public function deleteEmployee(int $id): bool
    {
        $this->logger->info("Executing deleteEmployee method for ID: $id");

        try {
            $employee = $this->employeeRepository->findEmployeeById($id);
            if (!$employee) {
                throw new \InvalidArgumentException("Employee with ID $id not found for deletion.");
            }

            $this->employeeRepository->deleteEmployee($employee, true);
            $this->logger->info("Employee with ID $id successfully deleted.");

            return true;
        } catch (\InvalidArgumentException $e) {
            // Логируем только конкретное исключение валидации
            $this->logger->warning($e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            // Логируем общую ошибку и выбрасываем исключение
            $this->logger->error("An unexpected error occurred while deleting employee with ID $id: " . $e->getMessage());
            throw $e;
        }
    }


    //Получение полных данных
    public function formatEmployeeData(Employee $employee, bool $detailedLanguage = false): array
    {
        //$language = $employee->getEmployeeLanguageID();
        $employeeData = [
            'EmployeeID' => $employee->getEmployeeID(),
            'EmployeeName' => $employee->getEmployeeName(),
            'EmployeeLink' => $employee->getEmployeeLink(),
            'EmployeeJobTitle' => $employee->getEmployeeJobTitle(),
            'EmployeeDescription' => $employee->getEmployeeDescription(),
            'Social' => [
                'EmployeeLinkedIn' => $employee->getEmployeeLinkedIn(),
                'EmployeeInstagram' => $employee->getEmployeeInstagram(),
                'EmployeeFacebook' => $employee->getEmployeeFacebook(),
                'EmployeeTwitter' => $employee->getEmployeeTwitter(),
            ],
            'CategoryID' => $employee->getEmployeeCategoryID(),
        ];
        // Добавляем результат `formatLanguageData`, получая либо полный объект, либо только LanguageID
        $employeeData += $this->validationService->formatLanguageData($employee->getEmployeeLanguageID(), $detailedLanguage);

        return $employeeData;
    }

}
