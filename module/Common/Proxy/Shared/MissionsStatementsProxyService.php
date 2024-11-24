<?php

namespace Module\Common\Proxy\Shared;

use Module\Shared\MissionsStatements\Entity\MissionStatementTranslations;
use Module\Shared\MissionsStatements\Repository\MissionsStatementsRepository;
use Module\Shared\MissionsStatements\Repository\MissionStatementTranslationsRepository;
use Module\Shared\MissionsStatements\Service\MissionsStatementsValidationService;
use Psr\Log\LoggerInterface;

class MissionsStatementsProxyService
{
    private MissionsStatementsRepository $missionStatementRepository;
    private MissionStatementTranslationsRepository $translationRepository;
    private MissionsStatementsValidationService $missionStatementValidationService;
    private LoggerInterface $logger;

    public function __construct(
        MissionsStatementsRepository $missionStatementRepository,
        MissionStatementTranslationsRepository $translationRepository,
        MissionsStatementsValidationService $missionStatementValidationService,
        LoggerInterface $logger,

    ) {
        $this->missionStatementRepository = $missionStatementRepository;
        $this->translationRepository = $translationRepository;
        $this->missionStatementValidationService = $missionStatementValidationService;
        $this->logger = $logger;
    }

    /**
     * Получение всех языков.
     */
    public function getAllMissionsStatements(): array
    {
        try {
            $this->logger->info("Fetching all languages directly from repository.");
            $languages = $this->missionStatementRepository->findAllMissionsStatements();

            if (empty($languages)) {
                $this->logger->info("No MissionStatement found in the database.");
                return [];
            }

            return array_map(
                fn($language) => $this->missionStatementValidationService->formatmissionStatementData($language),
                $languages
            );
        } catch (\Exception $e) {
            $this->logger->error("Failed to fetch all missionStatement: " . $e->getMessage());
            throw new \RuntimeException("Unable to fetch all missionStatement.");
        }
    }

    /**
     * Получение языка по ID.
     */
    public function getMissionStatementById(int $missionStatementId, int $languageId): array
    {
        try {
            $this->logger->info("Fetching missionStatement with ID {$missionStatementId}.");
            $missionStatement = $this->missionStatementValidationService->validateMissionStatementExists($missionStatementId);

            // Указываем, что не требуется детализированный формат
            return $this->missionStatementValidationService->formatMissionStatementData($missionStatement, true, $languageId);
        } catch (\InvalidArgumentException $e) {
            $this->logger->warning("Validation error for MissionStatement ID {$missionStatementId}: " . $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error("Failed to fetch MissionStatement with ID {$missionStatementId}: " . $e->getMessage());
            throw new \RuntimeException("Unable to fetch MissionStatement with ID {$missionStatementId}.");
        }
    }


    /**
     * Валидация существования категории по ID.
     */
    public function validateMissionStatementExists(mixed $missionStatementId): array
    {
        try {
            $this->logger->info("Validating MissionStatement ID: {$missionStatementId}");
            $missionStatement = $this->missionStatementValidationService->validateMissionStatementExists($missionStatementId);

            // Получаем отформатированные данные
            $formattedMissionStatement = $this->missionStatementValidationService->formatMissionStatementData($missionStatement, true);

            // Проверяем наличие ключа 'missionStatementID'
            if (!isset($formattedMissionStatement['MissionsStatements']['MissionStatementID'])) {
                throw new \InvalidArgumentException("MissionStatement data does not contain 'MissionStatementID'.");
            }
            $this->logger->info("Validated MissionStatement: " . json_encode($formattedMissionStatement));
            return $formattedMissionStatement['MissionStatement'];
        } catch (\InvalidArgumentException $e) {
            $this->logger->warning("Validation failed for MissionStatement ID {$missionStatementId}: {$e->getMessage()}");
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error("Unexpected error during validation for MissionStatement ID {$missionStatementId}: {$e->getMessage()}");
            throw new \RuntimeException("An unexpected error occurred during MissionStatement validation.");
        }
    }


}
