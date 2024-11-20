<?php

namespace Module\Common\Proxy\Shared;

use Module\Shared\Industries\Entity\IndustryTranslations;
use Module\Shared\Industries\Repository\IndustriesRepository;
use Module\Shared\Industries\Repository\IndustryTranslationsRepository;
use Module\Shared\Industries\Service\IndustriesValidationService;
use Psr\Log\LoggerInterface;

class IndustriesProxyService
{
    private IndustriesRepository $industriesRepository;
    private IndustryTranslationsRepository $translationRepository;
    private IndustriesValidationService $industriesValidationService;
    private LoggerInterface $logger;

    public function __construct(
        IndustriesRepository $industriesRepository,
        IndustryTranslationsRepository $translationRepository,
        IndustriesValidationService $industriesValidationService,
        LoggerInterface $logger,

    ) {
        $this->industriesRepository = $industriesRepository;
        $this->translationRepository = $translationRepository;
        $this->industriesValidationService = $industriesValidationService;
        $this->logger = $logger;
    }

    /**
     * Получение всех языков.
     */
    public function getAllIndustries(): array
    {
        try {
            $this->logger->info("Fetching all languages directly from repository.");
            $languages = $this->industriesRepository->findAllIndustries();

            if (empty($languages)) {
                $this->logger->info("No Industries found in the database.");
                return [];
            }

            return array_map(
                fn($language) => $this->industriesValidationService->formatIndustryData($language),
                $languages
            );
        } catch (\Exception $e) {
            $this->logger->error("Failed to fetch all Industries: " . $e->getMessage());
            throw new \RuntimeException("Unable to fetch all Industries.");
        }
    }

    /**
     * Получение языка по ID.
     */
    public function getIndustryById(int $industryId, int $languageId): array
    {
        try {
            $this->logger->info("Fetching Industry with ID {$industryId}.");
            $industry = $this->industriesValidationService->validateIndustryExists($industryId);

            // Указываем, что не требуется детализированный формат
            return $this->industriesValidationService->formatIndustryData($industry, true, $languageId);
        } catch (\InvalidArgumentException $e) {
            $this->logger->warning("Validation error for Industry ID {$industryId}: " . $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error("Failed to fetch Industry with ID {$industryId}: " . $e->getMessage());
            throw new \RuntimeException("Unable to fetch Industry with ID {$industryId}.");
        }
    }


    /**
     * Валидация существования категории по ID.
     */
    public function validateIndustryExists(mixed $industryId): array
    {
        try {
            $this->logger->info("Validating Industry ID: {$industryId}");
            $industry = $this->industriesValidationService->validateIndustryExists($industryId);

            // Получаем отформатированные данные
            $formattedIndustry = $this->industriesValidationService->formatIndustryData($industry, true);

            // Проверяем наличие ключа 'IndustryID'
            if (!isset($formattedIndustry['Industries']['IndustryID'])) {
                throw new \InvalidArgumentException("Industry data does not contain 'IndustryID'.");
            }
            $this->logger->info("Validated industry: " . json_encode($formattedIndustry));
            return $formattedIndustry['Industries'];
        } catch (\InvalidArgumentException $e) {
            $this->logger->warning("Validation failed for Industry ID {$industryId}: {$e->getMessage()}");
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error("Unexpected error during validation for Industry ID {$industryId}: {$e->getMessage()}");
            throw new \RuntimeException("An unexpected error occurred during industry validation.");
        }
    }


}
