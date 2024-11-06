<?php

namespace Module\Common\Service;

use Module\Employees\Entity\Employee;
use Module\Employees\Repository\EmployeesRepository;
use Module\Common\Service\LanguagesValidationService;
use Module\Common\Service\CategoriesValidationService;
use Psr\Log\LoggerInterface;

class EmployeesValidationService
{
    private EmployeesRepository $employeeRepository;

    private LanguagesValidationService $languagesValidationService;
    private CategoriesValidationService $categoriesValidationService;
    private LoggerInterface $logger;

    public function __construct(EmployeesRepository $employeeRepository,
                                LanguagesValidationService $languagesValidationService,
                                CategoriesValidationService $categoriesValidationService,
                                LoggerInterface $logger)
    {
        $this->employeeRepository = $employeeRepository;
        $this->categoriesValidationService = $categoriesValidationService;
        $this->languagesValidationService = $languagesValidationService;
        $this->logger = $logger;
    }

    /**
     * Валидация данных сотрудника.
     *
     * @param array $data
     * @param bool $isNew
     * @throws \InvalidArgumentException если данные не валидны
     */

    public function validateEmployeeData(array $data): void
    {
//        if ($isNew) {
//            if (empty($data['EmployeeName'])) {
//                throw new \InvalidArgumentException("Field 'EmployeeName' is required.");
//            }
//            if (empty($data['EmployeeJobTitle'])) {
//                throw new \InvalidArgumentException("Field 'EmployeeJobTitle' is required.");
//            }
//            //EmployeeLink в другом методе
//            //CategoryID и LanguageID проверяются в других классах
//        }
        if (!empty($data['EmployeeLink']) &&!preg_match('/^[a-zA-Z0-9_-]+$/', $data['EmployeeLink'])) {
            $this->logger->error("Invalid characters in EmployeeLink.");
            throw new \InvalidArgumentException("EmployeeLink' can contain only letters, numbers, underscores, and hyphens.");
        }
        if (!empty($data['EmployeeName']) && !preg_match('/^[\p{L}\s]+$/u', $data['EmployeeName'])) {
            throw new \InvalidArgumentException("Field 'EmployeeName' can contain only letters and spaces.");
        }

        if (!empty($data['EmployeeJobTitle']) && !preg_match('/^[\p{L}\s0-9]+$/u', $data['EmployeeJobTitle'])) {
            throw new \InvalidArgumentException("Field 'EmployeeJobTitle' can contain only letters, spaces, and numbers.");
        }

        if (!empty($data['EmployeeDescription']) && !preg_match('/^[\p{L}0-9\s.,\/\\\\]+$/u', $data['EmployeeDescription'])) {
            throw new \InvalidArgumentException("Field 'EmployeeDescription' can contain only letters, spaces, numbers, dots, commas, and / \\ characters.");
        }

        foreach (['EmployeeLinkedIn', 'EmployeeInstagram', 'EmployeeFacebook', 'EmployeeTwitter'] as $field) {
            if (!empty($data[$field]) && !preg_match('/^[a-zA-Z0-9@._-]*$/', $data[$field])) {
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
    public function ensureUniqueEmployeeJobTitle(?string $employeeJobTitle, ?int $excludeId = null): void
    {
        // Проверка обязательности поля CategoryName
        if (empty($employeeJobTitle)) {
            $this->logger->error("EmployeeJobTitle is required.");
            throw new \InvalidArgumentException("Field 'EmployeeJobTitle' is required and cannot be empty.");
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
            'EmployeeJobTitle' => $employee->getEmployeeJobTitle(),
            'EmployeeDescription' => $employee->getEmployeeDescription(),
            'Social' => [
                'EmployeeLinkedIn' => $employee->getEmployeeLinkedIn(),
                'EmployeeInstagram' => $employee->getEmployeeInstagram(),
                'EmployeeFacebook' => $employee->getEmployeeFacebook(),
                'EmployeeTwitter' => $employee->getEmployeeTwitter(),
            ],
        ];
        // Добавляем результат `formatLanguageData`, получая либо полный объект, либо только LanguageID
        $employeeData += $this->languagesValidationService->formatLanguageData($employee->getEmployeeLanguageID(), $detailedLanguage);
        $employeeData += $this->categoriesValidationService->formatCategoryData($employee->getEmployeeCategoryID(), true);

        return $employeeData;
    }
}
