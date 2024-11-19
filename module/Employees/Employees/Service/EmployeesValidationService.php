<?php

namespace Module\Employees\Employees\Service;

use Module\Employees\Employees\Entity\Employee;
use Module\Employees\Employees\Repository\EmployeesRepository;
use Module\Employees\EmployeesJobTitle\Service\EmployeesJobTitleValidationService;
use Module\Common\Service\LanguagesProxyService;
use Module\Common\Service\CategoriesProxyService;
use Psr\Log\LoggerInterface;

class EmployeesValidationService
{
    private EmployeesRepository $employeeRepository;
    private LanguagesProxyService $languagesProxyService;
    private CategoriesProxyService $categoriesProxyService;
    private EmployeesJobTitleValidationService $employeesJobTitleValidationService;
    private LoggerInterface $logger;

    public function __construct(EmployeesRepository $employeeRepository,
                                LanguagesProxyService $languagesProxyService,
                                CategoriesProxyService $categoriesProxyService,
                                EmployeesJobTitleValidationService $employeesJobTitleValidationService,
                                LoggerInterface $logger)
    {
        $this->employeeRepository = $employeeRepository;
        $this->languagesProxyService = $languagesProxyService;
        $this->categoriesProxyService = $categoriesProxyService;
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

        // Получение данных о языке через прокси
        $languageId = $employee->getLanguageID();
        try {
            $languageData = $this->languagesProxyService->getLanguageById($languageId);
            $employeeData['Language'] = $languageData;
        } catch (\Exception $e) {
            $this->logger->warning("Failed to fetch language for Employee ID {$employee->getEmployeeID()}: " . $e->getMessage());
            $employeeData['Language'] = 'Language data unavailable.';
        }

        // Получение данных о категории через прокси
        $categoryId = $employee->getCategoryID();
        try {
            $categoryData = $this->categoriesProxyService->getCategoryById($categoryId, $languageId);
            $employeeData['Category'] = $categoryData;
            $this->logger->info("Category data for Employee ID {$employee->getEmployeeID()}: " . json_encode($categoryData));
        } catch (\Exception $e) {
            $this->logger->info("Category data for Employee ID {$employee->getEmployeeID()}: " . json_encode($categoryData));
            $this->logger->warning("Failed to fetch category for Employee ID {$employee->getEmployeeID()}: " . $e->getMessage());
            $employeeData['Category'] = 'Category data unavailable.';
        }

        //$this->employeesJobTitleValidationService->formatEmployeesJobTitleData($employee->getEmployeeJobTitleID(), true, $employee->getEmployeeLanguageID());
        // Получение данных о должности
        $jobTitleId = $employee->getEmployeeJobTitleID();
        try {
            $jobTitleData = $this->employeesJobTitleValidationService->formatEmployeesJobTitleData(
                $jobTitleId,
                true,
                $languageId
            );
            $employeeData['JobTitle'] = $jobTitleData;
        } catch (\Exception $e) {
            $this->logger->warning("Failed to fetch job title for Employee ID {$employee->getEmployeeID()}: " . $e->getMessage());
            $employeeData['JobTitle'] = 'Job title data unavailable.';
        }


        return $employeeData;
    }
}
