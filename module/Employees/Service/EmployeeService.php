<?php

namespace Module\Employees\Service;

use Module\Employees\Repository\EmployeeRepository;
use Module\Employees\Entity\Employee;
use Psr\Log\LoggerInterface;

class EmployeeService
{
    private EmployeeRepository $employeeRepository;
    private LoggerInterface $logger;

    public function __construct(EmployeeRepository $employeeRepository, LoggerInterface $logger)
    {
        $this->employeeRepository = $employeeRepository;
        $this->logger = $logger;
        $this->logger->info("EmployeeService instance created.");
    }

    /**
     * Валидация данных сотрудника
     *
     * @param array $data
     * @return void
     */
    private function validateEmployeeData(array $data): void
    {
        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $data['EmployeeLink'])) {
            throw new \InvalidArgumentException("Field 'EmployeeLink' can contain only letters, numbers, underscores, and hyphens.");
        }

        if (!preg_match('/^[a-zA-Z\s]+$/', $data['EmployeeName'])) {
            throw new \InvalidArgumentException("Field 'EmployeeName' can contain only letters and spaces.");
        }

        if (!preg_match('/^[a-zA-Z\s0-9]+$/', $data['EmployeeJobTitle'])) {
            throw new \InvalidArgumentException("Field 'EmployeeJobTitle' can contain only letters, spaces, and numbers.");
        }

        if (!empty($data['EmployeeDescription']) && !preg_match('/^[a-zA-Z\s0-9]+$/', $data['EmployeeDescription'])) {
            throw new \InvalidArgumentException("Field 'EmployeeDescription' can contain only letters, spaces, and numbers.");
        }

        foreach (['LinkedIn', 'Instagram', 'Facebook', 'Twitter'] as $field) {
            if (!empty($data[$field]) && !preg_match('/^[a-zA-Z0-9@._-]*$/', $data[$field])) {
                throw new \InvalidArgumentException("Field '{$field}' can contain only letters, numbers, @, ., _ and -.");
            }
        }

        if (!is_int($data['CategoryID'])) {
            throw new \InvalidArgumentException("Field 'CategoryID' must be an integer.");
        }
    }

    /**
     * Получение всех сотрудников
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
     * Добавление нового сотрудника
     *
     * @param array $data
     * @return Employee
     */
    public function addEmployee(array $data): Employee
    {
        // Вызываем метод валидации перед добавлением сотрудника
        $this->validateEmployeeData($data);

        $employee = new Employee();
        $employee->setEmployeeName($data['EmployeeName'])
            ->setEmployeeLink($data['EmployeeLink'])
            ->setEmployeeJobTitle($data['EmployeeJobTitle'])
            ->setEmployeeDescription($data['EmployeeDescription'] ?? null)
            ->setLinkedIn($data['LinkedIn'] ?? null)
            ->setInstagram($data['Instagram'] ?? null)
            ->setFacebook($data['Facebook'] ?? null)
            ->setTwitter($data['Twitter'] ?? null)
            ->setCategoryID($data['CategoryID'])
            ->setLanguageID($data['LanguageID']);

        // Сохраняем сотрудника в базе данных с параметром flush = true
        $this->employeeRepository->saveEmployee($employee, true);

        // Логируем успешное добавление сотрудника с его ID
        $this->logger->info("Employee '{$employee->getEmployeeName()}' added with ID: {$employee->getEmployeeID()}.");

        return $employee;
    }

    /**
     * Получение сотрудника по ID
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
     * Обновление данных сотрудника
     *
     * @param int $id
     * @param array $data
     * @return Employee|null
     */
    public function updateEmployee(int $id, array $data): ?Employee
    {
        $this->logger->info("Executing updateEmployee method for ID: $id");

        $this->validateEmployeeData($data);

        $employee = $this->employeeRepository->findEmployeeById($id);
        if (!$employee) {
            $this->logger->warning("Employee with ID $id not found for updating.");
            return null;
        }

        $employee->setEmployeeLink(strtolower(trim($data['EmployeeLink'])));
        $employee->setEmployeeName(ucwords(strtolower(trim($data['EmployeeName']))));
        $employee->setEmployeeJobTitle(trim($data['EmployeeJobTitle']));
        $employee->setEmployeeDescription($data['EmployeeDescription'] ?? null);
        $employee->setLinkedIn($data['LinkedIn'] ?? null);
        $employee->setInstagram($data['Instagram'] ?? null);
        $employee->setFacebook($data['Facebook'] ?? null);
        $employee->setTwitter($data['Twitter'] ?? null);
        $employee->setCategoryID($data['CategoryID']);
        $employee->setLanguageID($data['LanguageID']);

        $this->employeeRepository->saveEmployee($employee);
        $this->logger->info("Employee with ID $id successfully updated.");

        return $employee;
    }

    /**
     * Обновление конкретного поля сотрудника
     *
     * @param int $id
     * @param string $field
     * @param mixed $value
     * @return Employee|null
     */
    public function updateEmployeeField(int $id, string $field, $value): ?Employee
    {
        $this->logger->info("Executing updateEmployeeField method for ID: $id, Field: $field");

        $employee = $this->employeeRepository->findEmployeeById($id);
        if (!$employee) {
            $this->logger->warning("Employee with ID $id not found for field update.");
            return null;
        }

        $this->employeeRepository->updateEmployeeField($id, $field, $value);
        $this->logger->info("Employee with ID $id successfully updated for field $field.");

        return $employee;
    }

    /**
     * Удаление сотрудника по ID
     *
     * @param int $id
     * @return bool
     */
    public function deleteEmployee(int $id): bool
    {
        $this->logger->info("Executing deleteEmployee method for ID: $id");

        // Находим сотрудника по ID
        $employee = $this->employeeRepository->findEmployeeById($id);
        if (!$employee) {
            $this->logger->warning("Employee with ID $id not found for deletion.");
            return false; // Возвращаем false, если сотрудник не найден
        }

        // Удаляем сотрудника, передав flush = true для немедленного сохранения изменений
        $this->employeeRepository->deleteEmployee($employee, true);
        $this->logger->info("Employee with ID $id successfully deleted.");

        return true; // Возвращаем true при успешном удалении
    }

}
