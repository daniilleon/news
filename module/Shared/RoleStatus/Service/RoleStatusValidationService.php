<?php

namespace Module\Shared\RoleStatus\Service;

use Module\Shared\RoleStatus\Entity\RoleStatus;
use Module\Shared\RoleStatus\Entity\RoleStatusTranslations;
use Module\Shared\RoleStatus\Repository\RoleStatusRepository;
use Module\Shared\RoleStatus\Repository\RoleStatusTranslationsRepository;
use Module\Common\Service\LanguagesProxyService;
use Psr\Log\LoggerInterface;

class RoleStatusValidationService
{
    private RoleStatusRepository $roleStatusRepository;
    private RoleStatusTranslationsRepository $translationRepository;
    private LanguagesProxyService $languagesProxyService;
    private LoggerInterface $logger;

    public function __construct(
        RoleStatusRepository            $roleStatusRepository,
        RoleStatusTranslationsRepository $translationRepository,
        LanguagesProxyService $languagesProxyService,
        LoggerInterface                        $logger
    ) {
        $this->roleStatusRepository = $roleStatusRepository;
        $this->translationRepository = $translationRepository;
        $this->languagesProxyService = $languagesProxyService;
        $this->logger = $logger;
    }

    /**
     * Валидация поля RoleStatusCode.
     *
     * @param array $data
     * @throws \InvalidArgumentException если данные не валидны
     */
    public function validateRoleStatusCode(array $data): void
    {
        if (!empty($data['RoleStatusCode']) &&!preg_match('/^[a-zA-Z0-9_-]+$/', $data['RoleStatusCode'])) {
            $this->logger->error("Invalid characters in RoleStatusCode.");
            throw new \InvalidArgumentException("Field 'RoleStatusCode' can contain only letters, numbers, underscores, and hyphens.");
        }
    }

    /**
     * Валидация данных перевода должности.
     */
    public function validateRoleStatusTranslationData(array $data): void
    {
        // Проверка допустимых символов и длины для RoleStatusName, если он передан
        if (isset($data['RoleStatusName'])) {
            $roleStatusName = $data['RoleStatusName'];

            // Проверка на допустимые символы и длину
            if (!preg_match('#^[\p{L}0-9 _\-/]{1,50}$#u', $roleStatusName)) {
                $this->logger->error("Invalid characters or length in RoleStatusName.");
                throw new \InvalidArgumentException("Field 'RoleStatusName' can contain only letters, numbers, underscores, hyphens, spaces, slashes, and must be no more than 50 characters long.");
            }

            // Проверка, что RoleStatusName не состоит только из цифр
            if (preg_match('/^\d+$/', $roleStatusName)) {
                $this->logger->error("RoleStatusName cannot consist only of numbers.");
                throw new \InvalidArgumentException("Field 'RoleStatusName' cannot consist only of numbers.");
            }
        }
    }

    /**
     * Проверка на уникальность RoleStatusCode.
     *
     * @param string $roleStatusCode
     * @param int|null $excludeId ID должности для исключения (например, при обновлении)
     * @throws \InvalidArgumentException если такой RoleStatusCode уже существует
     */
    public function ensureUniqueRoleStatusCode(?string $roleStatusCode, ?int $excludeId = null): void
    {
        // Проверка, что RoleStatusCode передан
        if (empty($roleStatusCode)) {
            $this->logger->error("Field 'RoleStatusCode' is required.");
            throw new \InvalidArgumentException("Field 'RoleStatusCode' is required.");
        }

        // Проверка на уникальность RoleStatusCode
        $existingRoleStatus = $this->roleStatusRepository->findRoleStatusByCode($roleStatusCode);
        if ($existingRoleStatus && ($excludeId === null || $existingRoleStatus->getRoleStatusID() !== $excludeId)) {
            $this->logger->error("Duplicate RoleStatusCode found: " . $roleStatusCode);
            throw new \InvalidArgumentException("RoleStatusCode '{$roleStatusCode}' already exists.");
        }
    }

    /**
     * Проверка на уникальность RoleStatusName.
     *
     * @param string $roleStatusName
     * @param int|null $excludeId ID категории для исключения (например, при обновлении)
     * @throws \InvalidArgumentException если такой RoleStatusName уже существует
     */
    public function ensureUniqueRoleStatusName(?string $roleStatusName, ?int $excludeId = null): void
    {
        // Проверка обязательности поля RoleStatusName
        if (empty($roleStatusName)) {
            $this->logger->error("RoleStatusName is required.");
            throw new \InvalidArgumentException("Field 'RoleStatusName' is required and cannot be empty.");
        }
    }

    /**
     * Получение и проверка существования должности по ID.
     */
    public function validateRoleStatusExists(mixed $roleStatusID): RoleStatus
    {
        // Проверка на наличие $roleStatusID
        if ($roleStatusID === null) {
            $this->logger->error("Field 'RoleStatusID' is required.");
            throw new \InvalidArgumentException("Field 'RoleStatusID' is required.");
        }

        // Проверка на целочисленный тип ID
        if (!is_int($roleStatusID)) {
            $this->logger->error("Field 'RoleStatusID' must be an integer.");
            throw new \InvalidArgumentException("Field 'RoleStatusID' must be an integer.");
        }

        $roleStatus = $this->roleStatusRepository->findRoleStatusById($roleStatusID);
        if (!$roleStatus) {
            $this->logger->warning("RoleStatus with ID $roleStatusID not found.");
            throw new \InvalidArgumentException("RoleStatus with ID $roleStatusID not found.");
        }
        return $roleStatus;
    }

    /**
     * Получение и проверка уникальности перевода.
     */
    public function ensureUniqueTranslation(RoleStatus $roleStatus, int $languageId): void
    {
        $this->languagesProxyService->validateLanguageID($languageId);
        $existingTranslation = $this->translationRepository->findTranslationByRoleStatusAndLanguage($roleStatus, $languageId);

        if ($existingTranslation) {
            $this->logger->error("Translation for RoleStatus ID {$roleStatus->getRoleStatusID()} with Language ID {$languageId} already exists.");
            throw new \InvalidArgumentException("Translation for this language already exists for this RoleStatus.");
        }
    }

    /**
     * Форматирование данных категории для ответа.
     *
     * @param RoleStatus $roleStatus
     * @return array
     */
    public function formatRoleStatusData(RoleStatus $roleStatus, bool $detail = false, ?int $languageId = null): array
    {
        $roleStatusData = [
            'roleStatusID' => $roleStatus->getRoleStatusID(),
            'roleStatusCode' => $roleStatus->getRoleStatusCode(),
        ];

        // Если требуется детальная информация и указан язык, получаем перевод
        if ($detail && $languageId) {
            try {
                $this->languagesProxyService->getLanguageById($languageId);
                $translation = $this->getRoleStatusTranslations($roleStatus, $languageId);

                $roleStatusData['Translation'] = $translation
                    ? $this->formatRoleStatusTranslationsData($translation)
                    : 'Translation not available for the selected language.';

            } catch (\Exception $e) {
                $this->logger->warning("Failed to fetch translation for category ID {$roleStatus->getRoleStatusID()}: " . $e->getMessage());
                $roleStatusData['Translation'] = 'Language details unavailable.';
            }
        }
        return $detail ? ['Categories' => $roleStatusData] : $roleStatusData;
    }

    /**
     * Форматирование данных перевода категории для ответа.
     *
     * @param RoleStatusTranslations $translation
     * @return array
     */
    public function formatRoleStatusTranslationsData(RoleStatusTranslations $translation): array
    {
        return [
            'RoleStatusTranslationID' => $translation->getRoleStatusTranslationID(),
            'LanguageID' => $translation->getLanguageID(),
            'RoleStatusName' => $translation->getRoleStatusName(),
        ];
    }

    public function getRoleStatusTranslations(RoleStatus $roleStatus, int $languageId): ?RoleStatusTranslations
    {
        $translation = $this->translationRepository->findTranslationByRoleStatusAndLanguage($roleStatus, $languageId);

        if (!$translation) {
            $this->logger->info("No translation found for category ID {$roleStatus->getRoleStatusID()} and language ID {$languageId}.");
        } else {
            $this->logger->info("Translation found: " . json_encode([
                    'RoleStatusID' => $roleStatus->getRoleStatusID(),
                    'LanguageID' => $languageId,
                    'TranslationID' => $translation->getRoleStatusTranslationID(),
                ]));
        }
        return $translation;
    }
}