<?php

namespace Module\Employees\EmployeesJobTitle\Service;

use Module\Employees\EmployeesJobTitle\Entity\EmployeeJobTitleTranslations;
use Module\Employees\EmployeesJobTitle\Entity\EmployeesJobTitle;
use Module\Employees\EmployeesJobTitle\Repository\EmployeeJobTitleTranslationsRepository;
use Module\Employees\EmployeesJobTitle\Repository\EmployeesJobTitleRepository;
use Module\Common\Proxy\Core\LanguagesProxyService;
use Psr\Log\LoggerInterface;

class EmployeesJobTitleValidationService
{
    private EmployeesJobTitleRepository $employeesJobTitleRepository;
    private EmployeeJobTitleTranslationsRepository $translationRepository;
    private LanguagesProxyService $languagesProxyService;
    private LoggerInterface $logger;

    public function __construct(
        EmployeesJobTitleRepository            $employeesJobTitleRepository,
        EmployeeJobTitleTranslationsRepository $translationRepository,
        LanguagesProxyService         $languagesProxyService,
        LoggerInterface                        $logger
    ) {
        $this->employeesJobTitleRepository = $employeesJobTitleRepository;
        $this->translationRepository = $translationRepository;
        $this->languagesProxyService = $languagesProxyService;
        $this->logger = $logger;
    }

    /**
     * Валидация поля EmployeeJobTitleCode.
     *
     * @param array $data
     * @throws \InvalidArgumentException если данные не валидны
     */
    public function validateEmployeeJobTitleCode(array $data): void
    {
        if (!empty($data['EmployeeJobTitleCode']) &&!preg_match('/^[a-zA-Z0-9_-]+$/', $data['EmployeeJobTitleCode'])) {
            $this->logger->error("Invalid characters in EmployeeJobTitleCode.");
            throw new \InvalidArgumentException("Field 'EmployeeJobTitleCode' can contain only letters, numbers, underscores, and hyphens.");
        }
    }

    /**
     * Валидация данных перевода должности.
     */
    public function validateEmployeeJobTitleTranslationData(array $data): void
    {
        // Проверка допустимых символов и длины для EmployeeJobTitleName, если он передан
        if (isset($data['EmployeeJobTitleName'])) {
            $employeeJobTitleName = $data['EmployeeJobTitleName'];

            // Проверка на допустимые символы и длину
            if (!preg_match('/^[\p{L}0-9 _-]{1,50}$/u', $employeeJobTitleName)) {
                $this->logger->error("Invalid characters or length in EmployeeJobTitleName.");
                throw new \InvalidArgumentException("Field 'EmployeeJobTitleName' can contain only letters, numbers, underscores, hyphens, spaces, and must be no more than 50 characters long.");
            }

            // Проверка, что EmployeeJobTitleName не состоит только из цифр
            if (preg_match('/^\d+$/', $employeeJobTitleName)) {
                $this->logger->error("EmployeeJobTitleName cannot consist only of numbers.");
                throw new \InvalidArgumentException("Field 'EmployeeJobTitleName' cannot consist only of numbers.");
            }
        }
    }

    /**
     * Проверка на уникальность EmployeeJobTitleCode.
     *
     * @param string $employeeJobTitleCode
     * @param int|null $excludeId ID должности для исключения (например, при обновлении)
     * @throws \InvalidArgumentException если такой EmployeeJobTitleCode уже существует
     */
    public function ensureUniqueEmployeeJobTitleCode(?string $employeeJobTitleCode, ?int $excludeId = null): void
    {
        // Проверка, что EmployeeJobTitleCode передан
        if (empty($employeeJobTitleCode)) {
            $this->logger->error("Field 'EmployeeJobTitleCode' is required.");
            throw new \InvalidArgumentException("Field 'EmployeeJobTitleCode' is required.");
        }

        // Проверка на уникальность EmployeeJobTitleCode
        $existingEmployeeJobTitle = $this->employeesJobTitleRepository->findEmployeeJobTitleByCode($employeeJobTitleCode);
        if ($existingEmployeeJobTitle && ($excludeId === null || $existingEmployeeJobTitle->getEmployeeJobTitleID() !== $excludeId)) {
            $this->logger->error("Duplicate EmployeeJobTitleCode found: " . $employeeJobTitleCode);
            throw new \InvalidArgumentException("EmployeeJobTitleCode '{$employeeJobTitleCode}' already exists.");
        }
    }

    /**
     * Проверка на уникальность EmployeeJobTitleNameName.
     *
     * @param string $employeeJobTitleName
     * @param int|null $excludeId ID категории для исключения (например, при обновлении)
     * @throws \InvalidArgumentException если такой EmployeeJobTitleName уже существует
     */
    public function ensureUniqueEmployeeJobTitleName(?string $employeeJobTitleName, ?int $excludeId = null): void
    {
        // Проверка обязательности поля EmployeeJobTitleName
        if (empty($employeeJobTitleName)) {
            $this->logger->error("EmployeeJobTitleName is required.");
            throw new \InvalidArgumentException("Field 'EmployeeJobTitleName' is required and cannot be empty.");
        }
    }

    /**
     * Получение и проверка существования должности по ID.
     */
    public function validateEmployeeJobTitleExists(mixed $employeeJobTitleID): EmployeesJobTitle
    {
        // Проверка на наличие $employeeJobTitleID
        if ($employeeJobTitleID === null) {
            $this->logger->error("Field 'EmployeeJobTitleID' is required.");
            throw new \InvalidArgumentException("Field 'EmployeeJobTitleID' is required.");
        }

        // Проверка на целочисленный тип ID
        if (!is_int($employeeJobTitleID)) {
            $this->logger->error("Field 'EmployeeJobTitleID' must be an integer.");
            throw new \InvalidArgumentException("Field 'EmployeeJobTitleID' must be an integer.");
        }

        $employeeJobTitle = $this->employeesJobTitleRepository->findEmployeeJobTitleById($employeeJobTitleID);
        if (!$employeeJobTitle) {
            $this->logger->warning("EmployeeJobTitle with ID $employeeJobTitleID not found.");
            throw new \InvalidArgumentException("EmployeeJobTitle with ID $employeeJobTitleID not found.");
        }
        return $employeeJobTitle;
    }

    /**
     * Получение и проверка уникальности перевода.
     */
    public function ensureUniqueTranslation(EmployeesJobTitle $employeeJobTitle, int $languageId): void
    {
        // Валидация языка через прокси
        $this->languagesProxyService->validateLanguageID($languageId);
        $existingTranslation = $this->translationRepository->findTranslationsByEmployeeJobTitleAndLanguage($employeeJobTitle, $languageId);

        if ($existingTranslation) {
            $this->logger->error("Translation for EmployeeJobTitle ID {$employeeJobTitle->getEmployeeJobTitleID()} with Language ID {$language->getLanguageID()} already exists.");
            throw new \InvalidArgumentException("Translation for this language already exists for this EmployeeJobTitle.");
        }
    }

    /**
     * Форматирование данных должности для ответа.
     *
     * @param EmployeesJobTitle $employeeJobTitle
     * @return array
     */
    public function formatEmployeesJobTitleData(EmployeesJobTitle $employeeJobTitle, bool $detail = false, ?int $languageId = null): array
    {
        $employeeJobTitleData = [
            'EmployeeJobTitleID' => $employeeJobTitle->getEmployeeJobTitleID(),
            'EmployeeJobTitleCode' => $employeeJobTitle->getEmployeeJobTitleCode(),
        ];

        // Если требуется детальная информация и указан язык, получаем перевод
        if ($detail && $languageId) {
            try{
                $this->languagesProxyService->getLanguageById($languageId);
                $translation = $this->getJobTitleTranslation($employeeJobTitle, $languageId);

                $employeeJobTitleData['Translation'] = $translation
                    ? $this->formatEmployeeJobTitleTranslationData($translation)
                    : 'Translation not available for the selected language.';

            } catch (\Exception $e) {
                $employeeJobTitleData['Translation'] = 'Language details unavailable.';
            }
        }

        return $detail ? ['EmployeesJobTitle' => $employeeJobTitleData] : $employeeJobTitleData;
    }


    /**
     * Форматирование данных перевода категории для ответа.
     *
     * @param EmployeeJobTitleTranslations $translation
     * @return array
     */
    public function formatEmployeeJobTitleTranslationData(EmployeeJobTitleTranslations $translation): array
    {
        return [
            'EmployeeJobTitleID' => $translation->getEmployeeJobTitleTranslationID(),
            'LanguageID' => $translation->getLanguageID(),
            'EmployeeJobTitleName' => $translation->getEmployeeJobTitleName(),
        ];
    }

    public function getJobTitleTranslation(EmployeesJobTitle $employeeJobTitle, int $languageId): ?EmployeeJobTitleTranslations
    {
        return $this->translationRepository->findTranslationsByEmployeeJobTitleAndLanguage($employeeJobTitle, $languageId);
    }


}
