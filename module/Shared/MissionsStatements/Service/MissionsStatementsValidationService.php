<?php

namespace Module\Shared\MissionsStatements\Service;

use Module\Shared\MissionsStatements\Entity\MissionsStatements;
use Module\Shared\MissionsStatements\Entity\MissionStatementTranslations;
use Module\Shared\MissionsStatements\Repository\MissionsStatementsRepository;
use Module\Shared\MissionsStatements\Repository\MissionStatementTranslationsRepository;
use Module\Common\Proxy\Core\LanguagesProxyService;
use Psr\Log\LoggerInterface;

class MissionsStatementsValidationService
{
    private MissionsStatementsRepository $missionStatementRepository;
    private MissionStatementTranslationsRepository $translationRepository;
    private LanguagesProxyService $languagesProxyService;
    private LoggerInterface $logger;

    public function __construct(
        MissionsStatementsRepository             $missionStatementRepository,
        MissionStatementTranslationsRepository $translationRepository,
        LanguagesProxyService                 $languagesProxyService,
        LoggerInterface                       $logger
    ) {
        $this->missionStatementRepository = $missionStatementRepository;
        $this->translationRepository = $translationRepository;
        $this->languagesProxyService = $languagesProxyService;
        $this->logger = $logger;
    }

    /**
     * Валидация поля MissionStatementCode.
     *
     * @param array $data
     * @throws \InvalidArgumentException если данные не валидны
     */
    public function validateMissionStatementCode(array $data): void
    {
        if (!empty($data['MissionStatementCode']) &&!preg_match('/^[a-zA-Z0-9_-]+$/', $data['MissionStatementCode'])) {
            $this->logger->error("Invalid characters in MissionStatementCode.");
            throw new \InvalidArgumentException("Field 'MissionStatementCode' can contain only letters, numbers, underscores, and hyphens.");
        }
    }

    /**
     * Валидация данных перевода должности.
     */
    public function validateMissionStatementTranslationData(array $data): void
    {
        // Проверка допустимых символов и длины для MissionStatementName, если он передан
        if (isset($data['MissionStatementName'])) {
            $missionStatementName = $data['MissionStatementName'];

            // Проверка на допустимые символы и длину
            if (!preg_match('#^[\p{L}0-9 _\-/]{1,50}$#u', $missionStatementName)) {
                $this->logger->error("Invalid characters or length in MissionStatementName.");
                throw new \InvalidArgumentException("Field 'MissionStatementName' can contain only letters, numbers, underscores, hyphens, spaces, slashes, and must be no more than 50 characters long.");
            }

            // Проверка, что MissionStatementName не состоит только из цифр
            if (preg_match('/^\d+$/', $missionStatementName)) {
                $this->logger->error("MissionStatementName cannot consist only of numbers.");
                throw new \InvalidArgumentException("Field 'MissionStatementName' cannot consist only of numbers.");
            }
        }
        // Если нужна проверка других полей, добавляем их сюда
        if (isset($data['MissionStatementDescription']) && strlen($data['MissionStatementDescription']) > 500) { // пример ограничения
            $this->logger->error("MissionStatementDescription is too long.");
            throw new \InvalidArgumentException("Field 'MissionStatementDescription' cannot exceed 500 characters.");
        }
    }

    /**
     * Проверка на уникальность MissionStatementCode.
     *
     * @param string $missionStatementCode
     * @param int|null $excludeId ID должности для исключения (например, при обновлении)
     * @throws \InvalidArgumentException если такой MissionStatementCode уже существует
     */
    public function ensureUniqueMissionStatementCode(?string $missionStatementCode, ?int $excludeId = null): void
    {
        // Проверка, что MissionStatementCode передан
        if (empty($missionStatementCode)) {
            $this->logger->error("Field 'MissionStatementCode' is required.");
            throw new \InvalidArgumentException("Field 'MissionStatementCode' is required.");
        }

        // Проверка на уникальность MissionStatementCode
        $existingMissionStatement = $this->missionStatementRepository->findMissionStatementByCode($missionStatementCode);
        if ($existingMissionStatement && ($excludeId === null || $existingMissionStatement->getMissionStatementID() !== $excludeId)) {
            $this->logger->error("Duplicate MissionStatementCode found: " . $missionStatementCode);
            throw new \InvalidArgumentException("MissionStatementCode '{$missionStatementCode}' already exists.");
        }
    }

    /**
     * Проверка на уникальность MissionStatementName.
     *
     * @param string $missionStatementName
     * @param int|null $excludeId ID категории для исключения (например, при обновлении)
     * @throws \InvalidArgumentException если такой MissionStatementName уже существует
     */
    public function ensureUniqueMissionStatementName(?string $missionStatementName, ?int $excludeId = null): void
    {
        // Проверка обязательности поля MissionStatementName
        if (empty($missionStatementName)) {
            $this->logger->error("MissionStatementName is required.");
            throw new \InvalidArgumentException("Field 'MissionStatementName' is required and cannot be empty.");
        }
    }

    /**
     * Получение и проверка существования должности по ID.
     */
    public function validateMissionStatementExists(mixed $missionStatementID): MissionsStatements
    {
        // Проверка на наличие $missionStatementID
        if ($missionStatementID === null) {
            $this->logger->error("Field 'MissionStatementID' is required.");
            throw new \InvalidArgumentException("Field 'MissionStatementID' is required.");
        }

        // Проверка на целочисленный тип ID
        if (!is_int($missionStatementID)) {
            $this->logger->error("Field 'MissionStatementID' must be an integer.");
            throw new \InvalidArgumentException("Field 'MissionStatementID' must be an integer.");
        }

        $missionStatement = $this->missionStatementRepository->findMissionStatementById($missionStatementID);
        if (!$missionStatement) {
            $this->logger->warning("MissionStatement with ID $missionStatementID not found.");
            throw new \InvalidArgumentException("MissionStatement with ID $missionStatementID not found.");
        }
        return $missionStatement;
    }

    /**
     * Получение и проверка уникальности перевода.
     */
    public function ensureUniqueTranslation(MissionsStatements $missionStatement, int $languageId): void
    {
        $this->languagesProxyService->validateLanguageID($languageId);
        $existingTranslation = $this->translationRepository->findTranslationByMissionStatementAndLanguage($missionStatement, $languageId);

        if ($existingTranslation) {
            $this->logger->error("Translation for MissionStatement ID {$missionStatement->getMissionStatementID()} with Language ID {$languageId} already exists.");
            throw new \InvalidArgumentException("Translation for this language already exists for this MissionStatement.");
        }
    }

    /**
     * Форматирование данных категории для ответа.
     *
     * @param MissionsStatements $missionStatement
     * @return array
     */
    public function formatMissionStatementData(MissionsStatements $missionStatement, bool $detail = false, ?int $languageId = null): array
    {
        $missionStatementData = [
            'MissionStatementID' => $missionStatement->getMissionStatementID(),
            'MissionStatementCode' => $missionStatement->getMissionStatementCode(),
        ];

        // Если требуется детальная информация и указан язык, получаем перевод
        if ($detail && $languageId) {
            try {
                $this->languagesProxyService->getLanguageById($languageId);
                $translation = $this->getMissionStatementTranslations($missionStatement, $languageId);

                $missionStatementData['Translation'] = $translation
                    ? $this->formatMissionStatementTranslationsData($translation)
                    : 'Translation not available for the selected language.';

            } catch (\Exception $e) {
                $this->logger->warning("Failed to fetch translation for MissionStatement ID {$missionStatement->getMissionStatementID()}: " . $e->getMessage());
                $missionStatementData['Translation'] = 'Language details unavailable.';
            }
        }
        return $detail ? ['MissionsStatements' => $missionStatementData] : $missionStatementData;
    }

    /**
     * Форматирование данных перевода категории для ответа.
     *
     * @param MissionStatementTranslations $translation
     * @return array
     */
    public function formatMissionStatementTranslationsData(MissionStatementTranslations $translation): array
    {
        return [
            'MissionStatementTranslationID' => $translation->getMissionStatementTranslationID(),
            'LanguageID' => $translation->getLanguageID(),
            'MissionStatementName' => $translation->getMissionStatementName(),
            'MissionStatementDescription' => $translation->getMissionStatementDescription(),
        ];
    }

    public function getMissionStatementTranslations(MissionsStatements $missionStatement, int $languageId): ?MissionStatementTranslations
    {
        $translation = $this->translationRepository->findTranslationByMissionStatementAndLanguage($missionStatement, $languageId);

        if (!$translation) {
            $this->logger->info("No translation found for MissionStatement ID {$missionStatement->getMissionStatementID()} and language ID {$languageId}.");
        } else {
            $this->logger->info("Translation found: " . json_encode([
                    'MissionStatementID' => $missionStatement->getMissionStatementID(),
                    'LanguageID' => $languageId,
                    'TranslationID' => $translation->getMissionStatementTranslationID(),
                ]));
        }

        return $translation;
    }
}
