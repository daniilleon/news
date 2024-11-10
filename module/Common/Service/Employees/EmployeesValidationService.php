<?php

namespace Module\Common\Service\Employees;

use Module\Employees\Entity\Employee;
use Module\Employees\Repository\EmployeesRepository;
use Module\Common\Service\LanguagesValidationService;
use Module\Common\Service\CategoriesValidationService;
use Module\Common\Service\Employees\EmployeesJobTitleValidationService;
use Psr\Log\LoggerInterface;

class EmployeesValidationService
{
    private EmployeesRepository $employeeRepository;

    private LanguagesValidationService $languagesValidationService;
    private CategoriesValidationService $categoriesValidationService;
    private EmployeesJobTitleValidationService $employeesJobTitleValidationService;
    private LoggerInterface $logger;

    public function __construct(EmployeesRepository $employeeRepository,
                                LanguagesValidationService $languagesValidationService,
                                CategoriesValidationService $categoriesValidationService,
                                EmployeesJobTitleValidationService $employeesJobTitleValidationService,
                                LoggerInterface $logger)
    {
        $this->employeeRepository = $employeeRepository;
        $this->categoriesValidationService = $categoriesValidationService;
        $this->languagesValidationService = $languagesValidationService;
        $this->employeesJobTitleValidationService = $employeesJobTitleValidationService;
        $this->logger = $logger;
    }

    /**
     * Валидация данных сотрудника.
     *
     * @param array $data
     * @throws \InvalidArgumentException если данные не валидны
     */

    public function validateEmployeeData(array $data): void
    {
        //CategoryID и LanguageID проверяются в других классах
        if (!empty($data['EmployeeLink']) &&!preg_match('/^[a-zA-Z0-9_-]+$/', $data['EmployeeLink'])) {
            $this->logger->error("Invalid characters in EmployeeLink.");
            throw new \InvalidArgumentException("EmployeeLink' can contain only letters, numbers, underscores, and hyphens.");
        }
        if (!empty($data['EmployeeName']) && !preg_match('/^[\p{L}\s]+$/u', $data['EmployeeName'])) {
            $this->logger->error("Invalid characters in EmployeeName.");
            throw new \InvalidArgumentException("Field 'EmployeeName' can contain only letters and spaces.");
        }

        if (!empty($data['EmployeeDescription']) && !preg_match('/^[\p{L}0-9\s.,\/\\\\]+$/u', $data['EmployeeDescription'])) {
            $this->logger->error("Invalid characters in EmployeeDescription.");
            throw new \InvalidArgumentException("Field 'EmployeeDescription' can contain only letters, spaces, numbers, dots, commas, and / \\ characters.");
        }

        foreach (['EmployeeLinkedIn', 'EmployeeInstagram', 'EmployeeFacebook', 'EmployeeTwitter'] as $field) {
            if (!empty($data[$field]) && !preg_match('/^[a-zA-Z0-9@._-]*$/', $data[$field])) {
                $this->logger->error("Invalid characters in '{$field}'.");
                throw new \InvalidArgumentException("Field '{$field}' can contain only letters, numbers, @, ., _ and -.");
            }
        }
    }

    public function ensureUniqueEmployeeName(?string $employeeName, ?int $excludeId = null): void
    {
        // Проверка обязательности поля CategoryName
        if (empty($employeeName)) {
            $this->logger->error("EmployeeName is required.");
            throw new \InvalidArgumentException("Field 'EmployeeName' is required and cannot be empty.");
        }
    }

    public function ensureUniqueEmployeeActive($employeeActive): void
    {
        // Проверка на отсутствие значения
        if ($employeeActive === null) {
            $this->logger->error("EmployeeActive is required.");
            throw new \InvalidArgumentException("Field 'EmployeeActive' is required and cannot be empty.");
        }

        // Проверка на строгое булевое значение
        if (!is_bool($employeeActive)) {
            $this->logger->error("Invalid value in EmployeeActive.");
            throw new \InvalidArgumentException("Field 'EmployeeActive' must be a boolean value (true or false).");
        }
    }


    //Проверка о том, активен ли сотрудник
    public function ensureEmployeeActive(int $employeeId): void
    {
        // Находим сотрудника по ID
        $employee = $this->employeeRepository->findEmployeeById($employeeId);

        // Проверяем, существует ли сотрудник и активен ли он
        if (!$employee) {
            $this->logger->warning("Employee with ID {$employeeId} not found.");
            throw new \InvalidArgumentException("Employee with ID {$employeeId} not found.");
        }

        if (!$employee->getEmployeeActive()) {
            $this->logger->warning("Employee with ID {$employeeId} is inactive.");
            throw new \InvalidArgumentException("Field 'EmployeeActive' must be active for this operation.");
        }
    }

    /**
     * Проверка на уникальность EmployeeLink.
     *
     * @param string $employeeLink
     * @param int|null $excludeId ID сотрудника для исключения (например, при обновлении)
     * @throws \InvalidArgumentException если такой EmployeeLink уже существует
     */
    public function ensureUniqueEmployeeLink(?string $employeeLink, ?int $excludeId = null): void
    {
        // Проверка, что EmployeeLink передан
        if (empty($employeeLink)) {
            $this->logger->error("Field 'EmployeeLink' is required.");
            throw new \InvalidArgumentException("Field 'EmployeeLink' is required.");
        }

        // Проверка на уникальность EmployeeLink
        $existingEmployee = $this->employeeRepository->findEmployeeByLink($employeeLink);
        if ($existingEmployee && ($excludeId === null || $existingEmployee->getEmployeeID() !== $excludeId)) {
            $this->logger->error("Duplicate EmployeeLink found: " . $employeeLink);
            throw new \InvalidArgumentException("EmployeeLink '{$employeeLink}' already exists.");
        }
    }



    /**
     * Валидация ID полей для сущностей.
     *
     * @param mixed $id
     * @param string $fieldName
     * @throws \InvalidArgumentException
     */
    public function validateEntityID($id, string $fieldName): void
    {
        // Проверка на целочисленный тип ID
        if (!is_int($id)) {
            $this->logger->error("Field '$fieldName' must be an integer.");
            throw new \InvalidArgumentException("Field '$fieldName' must be an integer.");
        }
    }

    public function formatEmployeeData(Employee $employee, bool $detailedLanguage = true): array
    {
        //$language = $employee->getEmployeeLanguageID();
        $employeeData = [
            'EmployeeID' => $employee->getEmployeeID(),
            'EmployeeName' => $employee->getEmployeeName(),
            'EmployeeLink' => $employee->getEmployeeLink(),
            'EmployeeDescription' => $employee->getEmployeeDescription(),
            'Social' => [
                'EmployeeLinkedIn' => $employee->getEmployeeLinkedIn(),
                'EmployeeInstagram' => $employee->getEmployeeInstagram(),
                'EmployeeFacebook' => $employee->getEmployeeFacebook(),
                'EmployeeTwitter' => $employee->getEmployeeTwitter(),
            ],
            'EmployeeActive' => $employee->getEmployeeActive(),
        ];
        // Добавляем результат `formatLanguageData`, получая либо полный объект, либо только LanguageID
        // Вызов formatEmployeeData, где используется formatEmployeesJobTitleData
        $employeeData += $this->employeesJobTitleValidationService->formatEmployeesJobTitleData($employee->getEmployeeJobTitleID(), true, $employee->getEmployeeLanguageID());
        $employeeData += $this->languagesValidationService->formatLanguageData($employee->getEmployeeLanguageID(), $detailedLanguage);
        $employeeData += $this->categoriesValidationService->formatCategoryData($employee->getEmployeeCategoryID(), true);

        return $employeeData;
    }
}
