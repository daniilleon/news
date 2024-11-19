<?php

namespace Module\Persons\MaritalStatus\Service;

use Module\Persons\MaritalStatus\Entity\MaritalStatus;
use Module\Persons\MaritalStatus\Entity\MaritalStatusTranslations;
use Module\Persons\MaritalStatus\Repository\MaritalStatusRepository;
use Module\Persons\MaritalStatus\Repository\MaritalStatusTranslationsRepository;
use Module\Common\Service\LanguagesProxyService;
use Psr\Log\LoggerInterface;

class MaritalStatusValidationService
{
    private MaritalStatusRepository $maritalStatusRepository;
    private MaritalStatusTranslationsRepository $translationRepository;
    private LanguagesProxyService $languagesProxyService;
    private LoggerInterface $logger;

    public function __construct(
        MaritalStatusRepository            $maritalStatusRepository,
        MaritalStatusTranslationsRepository $translationRepository,
        LanguagesProxyService $languagesProxyService,
        LoggerInterface                        $logger
    ) {
        $this->maritalStatusRepository = $maritalStatusRepository;
        $this->translationRepository = $translationRepository;
        $this->languagesProxyService = $languagesProxyService;
        $this->logger = $logger;
    }

    /**
     * Валидация поля MaritalStatusCode.
     *
     * @param array $data
     * @throws \InvalidArgumentException если данные не валидны
     */
    public function validateMaritalStatusCode(array $data): void
    {
        if (!empty($data['MaritalStatusCode']) &&!preg_match('/^[a-zA-Z0-9_-]+$/', $data['MaritalStatusCode'])) {
            $this->logger->error("Invalid characters in MaritalStatusCode.");
            throw new \InvalidArgumentException("Field 'MaritalStatusCode' can contain only letters, numbers, underscores, and hyphens.");
        }
    }

    /**
     * Валидация данных перевода должности.
     */
    public function validateMaritalStatusTranslationData(array $data): void
    {
        // Проверка допустимых символов и длины для MaritalStatusName, если он передан
        if (isset($data['MaritalStatusName'])) {
            $maritalStatusName = $data['MaritalStatusName'];

            // Проверка на допустимые символы и длину
            if (!preg_match('#^[\p{L}0-9 _\-/]{1,50}$#u', $maritalStatusName)) {
                $this->logger->error("Invalid characters or length in MaritalStatusName.");
                throw new \InvalidArgumentException("Field 'MaritalStatusName' can contain only letters, numbers, underscores, hyphens, spaces, slashes, and must be no more than 50 characters long.");
            }

            // Проверка, что MaritalStatusName не состоит только из цифр
            if (preg_match('/^\d+$/', $maritalStatusName)) {
                $this->logger->error("MaritalStatusName cannot consist only of numbers.");
                throw new \InvalidArgumentException("Field 'MaritalStatusName' cannot consist only of numbers.");
            }
        }
    }

    /**
     * Проверка на уникальность MaritalStatusCode.
     *
     * @param string $maritalStatusCode
     * @param int|null $excludeId ID должности для исключения (например, при обновлении)
     * @throws \InvalidArgumentException если такой MaritalStatusCode уже существует
     */
    public function ensureUniqueMaritalStatusCode(?string $maritalStatusCode, ?int $excludeId = null): void
    {
        // Проверка, что MaritalStatusCode передан
        if (empty($maritalStatusCode)) {
            $this->logger->error("Field 'MaritalStatusCode' is required.");
            throw new \InvalidArgumentException("Field 'MaritalStatusCode' is required.");
        }

        // Проверка на уникальность MaritalStatusCode
        $existingMaritalStatus = $this->maritalStatusRepository->findMaritalStatusByCode($maritalStatusCode);
        if ($existingMaritalStatus && ($excludeId === null || $existingMaritalStatus->getMaritalStatusID() !== $excludeId)) {
            $this->logger->error("Duplicate MaritalStatusCode found: " . $maritalStatusCode);
            throw new \InvalidArgumentException("MaritalStatusCode '{$maritalStatusCode}' already exists.");
        }
    }

    /**
     * Проверка на уникальность MaritalStatusName.
     *
     * @param string $maritalStatusName
     * @param int|null $excludeId ID категории для исключения (например, при обновлении)
     * @throws \InvalidArgumentException если такой MaritalStatusName уже существует
     */
    public function ensureUniqueMaritalStatusName(?string $maritalStatusName, ?int $excludeId = null): void
    {
        // Проверка обязательности поля MaritalStatusName
        if (empty($maritalStatusName)) {
            $this->logger->error("MaritalStatusName is required.");
            throw new \InvalidArgumentException("Field 'MaritalStatusName' is required and cannot be empty.");
        }
    }

    /**
     * Получение и проверка существования должности по ID.
     */
    public function validateMaritalStatusExists(mixed $maritalStatusID): MaritalStatus
    {
        // Проверка на наличие $maritalStatusID
        if ($maritalStatusID === null) {
            $this->logger->error("Field 'MaritalStatusID' is required.");
            throw new \InvalidArgumentException("Field 'MaritalStatusID' is required.");
        }

        // Проверка на целочисленный тип ID
        if (!is_int($maritalStatusID)) {
            $this->logger->error("Field 'MaritalStatusID' must be an integer.");
            throw new \InvalidArgumentException("Field 'MaritalStatusID' must be an integer.");
        }

        $maritalStatus = $this->maritalStatusRepository->findMaritalStatusById($maritalStatusID);
        if (!$maritalStatus) {
            $this->logger->warning("MaritalStatus with ID $maritalStatusID not found.");
            throw new \InvalidArgumentException("MaritalStatus with ID $maritalStatusID not found.");
        }
        return $maritalStatus;
    }

    /**
     * Получение и проверка уникальности перевода.
     */
    public function ensureUniqueTranslation(MaritalStatus $maritalStatus, int $languageId): void
    {
        $this->languagesProxyService->validateLanguageID($languageId);
        $existingTranslation = $this->translationRepository->findTranslationByMaritalStatusAndLanguage($maritalStatus, $languageId);

        if ($existingTranslation) {
            $this->logger->error("Translation for MaritalStatus ID {$maritalStatus->getMaritalStatusID()} with Language ID {$languageId} already exists.");
            throw new \InvalidArgumentException("Translation for this language already exists for this MaritalStatus.");
        }
    }

    /**
     * Форматирование данных категории для ответа.
     *
     * @param MaritalStatus $maritalStatus
     * @return array
     */
    public function formatMaritalStatusData(MaritalStatus $maritalStatus, bool $detail = false, ?int $languageId = null): array
    {
        $maritalStatusData = [
            'MaritalStatusID' => $maritalStatus->getMaritalStatusID(),
            'MaritalStatusCode' => $maritalStatus->getMaritalStatusCode(),
        ];

        // Если требуется детальная информация и указан язык, получаем перевод
        if ($detail && $languageId) {
            try {
                $this->languagesProxyService->getLanguageById($languageId);
                $translation = $this->getMaritalStatusTranslations($maritalStatus, $languageId);

                $maritalStatusData['Translation'] = $translation
                    ? $this->formatMaritalStatusTranslationsData($translation)
                    : 'Translation not available for the selected language.';

            } catch (\Exception $e) {
                $this->logger->warning("Failed to fetch translation for category ID {$maritalStatus->getMaritalStatusID()}: " . $e->getMessage());
                $maritalStatusData['Translation'] = 'Language details unavailable.';
            }
        }
        return $detail ? ['Categories' => $maritalStatusData] : $maritalStatusData;
    }

    /**
     * Форматирование данных перевода категории для ответа.
     *
     * @param MaritalStatusTranslations $translation
     * @return array
     */
    public function formatMaritalStatusTranslationsData(MaritalStatusTranslations $translation): array
    {
        return [
            'MaritalStatusTranslationID' => $translation->getMaritalStatusTranslationID(),
            'LanguageID' => $translation->getLanguageID(),
            'MaritalStatusName' => $translation->getMaritalStatusName(),
        ];
    }

    public function getMaritalStatusTranslations(MaritalStatus $maritalStatus, int $languageId): ?MaritalStatusTranslations
    {
        $translation = $this->translationRepository->findTranslationByMaritalStatusAndLanguage($maritalStatus, $languageId);

        if (!$translation) {
            $this->logger->info("No translation found for category ID {$maritalStatus->getMaritalStatusID()} and language ID {$languageId}.");
        } else {
            $this->logger->info("Translation found: " . json_encode([
                    'MaritalStatusID' => $maritalStatus->getMaritalStatusID(),
                    'LanguageID' => $languageId,
                    'TranslationID' => $translation->getMaritalStatusTranslationID(),
                ]));
        }

        return $translation;
    }


}
