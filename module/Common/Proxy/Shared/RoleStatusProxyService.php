<?php

namespace Module\Common\Proxy\Shared;

use Module\Shared\RoleStatus\Entity\RoleStatusTranslations;
use Module\Shared\RoleStatus\Repository\RoleStatusRepository;
use Module\Shared\RoleStatus\Repository\RoleStatusTranslationsRepository;
use Module\Shared\RoleStatus\Service\RoleStatusValidationService;
use Psr\Log\LoggerInterface;

class RoleStatusProxyService
{
    private RoleStatusRepository $roleStatusRepository;
    private RoleStatusTranslationsRepository $translationRepository;
    private RoleStatusValidationService $roleStatusValidationService;
    private LoggerInterface $logger;

    public function __construct(
        RoleStatusRepository $roleStatusRepository,
        RoleStatusTranslationsRepository $translationRepository,
        RoleStatusValidationService $roleStatusValidationService,
        LoggerInterface $logger,

    ) {
        $this->roleStatusRepository = $roleStatusRepository;
        $this->translationRepository = $translationRepository;
        $this->roleStatusValidationService = $roleStatusValidationService;
        $this->logger = $logger;
    }

    /**
     * Получение всех языков.
     */
    public function getAllRoleStatus(): array
    {
        try {
            $this->logger->info("Fetching all languages directly from repository.");
            $languages = $this->roleStatusRepository->findAllRoleStatus();

            if (empty($languages)) {
                $this->logger->info("No RoleStatus found in the database.");
                return [];
            }

            return array_map(
                fn($language) => $this->roleStatusValidationService->formatRoleStatusData($language),
                $languages
            );
        } catch (\Exception $e) {
            $this->logger->error("Failed to fetch all RoleStatus: " . $e->getMessage());
            throw new \RuntimeException("Unable to fetch all RoleStatus.");
        }
    }

    /**
     * Получение языка по ID.
     */
    public function getRoleStatusById(int $roleStatusId, int $languageId): array
    {
        try {
            $this->logger->info("Fetching RoleStatus with ID {$roleStatusId}.");
            $roleStatus = $this->roleStatusValidationService->validateRoleStatusExists($roleStatusId);

            // Указываем, что не требуется детализированный формат
            return $this->roleStatusValidationService->formatRoleStatusData($roleStatus, true, $languageId);
        } catch (\InvalidArgumentException $e) {
            $this->logger->warning("Validation error for RoleStatus ID {$roleStatusId}: " . $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error("Failed to fetch RoleStatus with ID {$roleStatusId}: " . $e->getMessage());
            throw new \RuntimeException("Unable to fetch RoleStatus with ID {$roleStatusId}.");
        }
    }


    /**
     * Валидация существования категории по ID.
     */
    public function validateRoleStatusExists(mixed $roleStatusId): array
    {
        try {
            $this->logger->info("Validating RoleStatus ID: {$roleStatusId}");
            $roleStatus = $this->roleStatusValidationService->validateRoleStatusExists($roleStatusId);

            // Получаем отформатированные данные
            $formattedRoleStatus = $this->roleStatusValidationService->formatRoleStatusData($roleStatus, true);

            // Проверяем наличие ключа 'RoleStatusID'
            if (!isset($formattedRoleStatus['RoleStatus']['RoleStatusID'])) {
                throw new \InvalidArgumentException("RoleStatus data does not contain 'RoleStatusID'.");
            }
            $this->logger->info("Validated RoleStatus: " . json_encode($formattedRoleStatus));
            return $formattedRoleStatus['RoleStatus'];
        } catch (\InvalidArgumentException $e) {
            $this->logger->warning("Validation failed for RoleStatus ID {$roleStatusId}: {$e->getMessage()}");
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error("Unexpected error during validation for RoleStatus ID {$roleStatusId}: {$e->getMessage()}");
            throw new \RuntimeException("An unexpected error occurred during RoleStatus validation.");
        }
    }


}
