<?php

namespace Module\Persons\EducationLevels\Service;

use Module\Persons\EducationLevels\Entity\EducationLevels;
use Module\Persons\EducationLevels\Entity\EducationLevelTranslations;
use Module\Persons\EducationLevels\Repository\EducationLevelsRepository;
use Module\Persons\EducationLevels\Repository\EducationLevelTranslationsRepository;
use Module\Common\Proxy\Core\LanguagesProxyService;
use Psr\Log\LoggerInterface;

class EducationLevelsValidationService
{
    private EducationLevelsRepository $educationLevelsRepository;
    private EducationLevelTranslationsRepository $translationRepository;
    private LanguagesProxyService $languagesProxyService;
    private LoggerInterface $logger;

    public function __construct(
        EducationLevelsRepository            $educationLevelsRepository,
        EducationLevelTranslationsRepository $translationRepository,
        LanguagesProxyService $languagesProxyService,
        LoggerInterface                        $logger
    ) {
        $this->educationLevelsRepository = $educationLevelsRepository;
        $this->translationRepository = $translationRepository;
        $this->languagesProxyService = $languagesProxyService;
        $this->logger = $logger;
    }

    /**
     * Валидация поля EducationLevelCode.
     *
     * @param array $data
     * @throws \InvalidArgumentException если данные не валидны
     */
    public function validateEducationLevelCode(array $data): void
    {
        if (!empty($data['EducationLevelCode']) &&!preg_match('/^[a-zA-Z0-9_-]+$/', $data['EducationLevelCode'])) {
            $this->logger->error("Invalid characters in EducationLevelCode.");
            throw new \InvalidArgumentException("Field 'EducationLevelCode' can contain only letters, numbers, underscores, and hyphens.");
        }
    }

    /**
     * Валидация данных перевода должности.
     */
    public function validateEducationLevelTranslationData(array $data): void
    {
        // Проверка допустимых символов и длины для EducationLevelName, если он передан
        if (isset($data['EducationLevelName'])) {
            $educationLevelName = $data['EducationLevelName'];

            // Проверка на допустимые символы и длину
            if (!preg_match('#^[\p{L}0-9 _\-/]{1,50}$#u', $educationLevelName)) {
                $this->logger->error("Invalid characters or length in EducationLevelName.");
                throw new \InvalidArgumentException("Field 'EducationLevelName' can contain only letters, numbers, underscores, hyphens, spaces, slashes, and must be no more than 50 characters long.");
            }

            // Проверка, что EducationLevelName не состоит только из цифр
            if (preg_match('/^\d+$/', $educationLevelName)) {
                $this->logger->error("EducationLevelName cannot consist only of numbers.");
                throw new \InvalidArgumentException("Field 'EducationLevelName' cannot consist only of numbers.");
            }
        }
    }

    /**
     * Проверка на уникальность EducationLevelCode.
     *
     * @param string $educationLevelCode
     * @param int|null $excludeId ID должности для исключения (например, при обновлении)
     * @throws \InvalidArgumentException если такой EducationLevelCode уже существует
     */
    public function ensureUniqueEducationLevelCode(?string $educationLevelCode, ?int $excludeId = null): void
    {
        // Проверка, что EducationLevelCode передан
        if (empty($educationLevelCode)) {
            $this->logger->error("Field 'EducationLevelCode' is required.");
            throw new \InvalidArgumentException("Field 'EducationLevelCode' is required.");
        }

        // Проверка на уникальность EducationLevelCode
        $existingEducationLevel = $this->educationLevelsRepository->findEducationLevelByCode($educationLevelCode);
        if ($existingEducationLevel && ($excludeId === null || $existingEducationLevel->getEducationLevelID() !== $excludeId)) {
            $this->logger->error("Duplicate EducationLevelCode found: " . $educationLevelCode);
            throw new \InvalidArgumentException("EducationLevelCode '{$educationLevelCode}' already exists.");
        }
    }

    /**
     * Проверка на уникальность EducationLevelName.
     *
     * @param string $educationLevelName
     * @param int|null $excludeId ID категории для исключения (например, при обновлении)
     * @throws \InvalidArgumentException если такой EducationLevelName уже существует
     */
    public function ensureUniqueEducationLevelName(?string $educationLevelName, ?int $excludeId = null): void
    {
        // Проверка обязательности поля EducationLevelName
        if (empty($educationLevelName)) {
            $this->logger->error("EducationLevelName is required.");
            throw new \InvalidArgumentException("Field 'EducationLevelName' is required and cannot be empty.");
        }
    }

    /**
     * Получение и проверка существования должности по ID.
     */
    public function validateEducationLevelExists(mixed $educationLevelID): EducationLevels
    {
        // Проверка на наличие $educationLevelID
        if ($educationLevelID === null) {
            $this->logger->error("Field 'EducationLevelID' is required.");
            throw new \InvalidArgumentException("Field 'EducationLevelID' is required.");
        }

        // Проверка на целочисленный тип ID
        if (!is_int($educationLevelID)) {
            $this->logger->error("Field 'EducationLevelID' must be an integer.");
            throw new \InvalidArgumentException("Field 'EducationLevelID' must be an integer.");
        }

        $educationLevel = $this->educationLevelsRepository->findEducationLevelById($educationLevelID);
        if (!$educationLevel) {
            $this->logger->warning("EducationLevel with ID $educationLevelID not found.");
            throw new \InvalidArgumentException("EducationLevel with ID $educationLevelID not found.");
        }
        return $educationLevel;
    }

    /**
     * Получение и проверка уникальности перевода.
     */
    public function ensureUniqueTranslation(EducationLevels $educationLevel, int $languageId): void
    {
        $this->languagesProxyService->validateLanguageID($languageId);
        $existingTranslation = $this->translationRepository->findTranslationByEducationLevelAndLanguage($educationLevel, $languageId);

        if ($existingTranslation) {
            $this->logger->error("Translation for EducationLevel ID {$educationLevel->getEducationLevelID()} with Language ID {$languageId} already exists.");
            throw new \InvalidArgumentException("Translation for this language already exists for this EducationLevel.");
        }
    }

    /**
     * Форматирование данных категории для ответа.
     *
     * @param EducationLevels $educationLevel
     * @return array
     */
    public function formatEducationLevelData(EducationLevels $educationLevel, bool $detail = false, ?int $languageId = null): array
    {
        $educationLevelData = [
            'EducationLevelID' => $educationLevel->getEducationLevelID(),
            'EducationLevelCode' => $educationLevel->getEducationLevelCode(),
        ];

        // Если требуется детальная информация и указан язык, получаем перевод
        if ($detail && $languageId) {
            try {
                $this->languagesProxyService->getLanguageById($languageId);
                $translation = $this->getEducationLevelTranslations($educationLevel, $languageId);

                $educationLevelData['Translation'] = $translation
                    ? $this->formatEducationLevelTranslationsData($translation)
                    : 'Translation not available for the selected language.';

            } catch (\Exception $e) {
                $this->logger->warning("Failed to fetch translation for category ID {$educationLevel->getEducationLevelID()}: " . $e->getMessage());
                $educationLevelData['Translation'] = 'Language details unavailable.';
            }
        }
        return $detail ? ['EducationLevels' => $educationLevelData] : $educationLevelData;
    }

    /**
     * Форматирование данных перевода категории для ответа.
     *
     * @param EducationLevelTranslations $translation
     * @return array
     */
    public function formatEducationLevelTranslationsData(EducationLevelTranslations $translation): array
    {
        return [
            'EducationLevelTranslationID' => $translation->getEducationLevelTranslationID(),
            'LanguageID' => $translation->getLanguageID(),
            'EducationLevelName' => $translation->getEducationLevelName(),
        ];
    }

    public function getEducationLevelTranslations(EducationLevels $educationLevel, int $languageId): ?EducationLevelTranslations
    {
        $translation = $this->translationRepository->findTranslationByEducationLevelAndLanguage($educationLevel, $languageId);

        if (!$translation) {
            $this->logger->info("No translation found for category ID {$educationLevel->getEducationLevelID()} and language ID {$languageId}.");
        } else {
            $this->logger->info("Translation found: " . json_encode([
                    'EducationLevelID' => $educationLevel->getEducationLevelID(),
                    'LanguageID' => $languageId,
                    'TranslationID' => $translation->getEducationLevelTranslationID(),
                ]));
        }

        return $translation;
    }
}
