<?php

namespace Module\Common\Service;

use Module\Languages\Repository\LanguagesRepository;
use Module\Languages\Service\LanguagesValidationService;
use Psr\Log\LoggerInterface;

class LanguagesProxyService
{
    private LanguagesRepository $languagesRepository;
    private LanguagesValidationService $languagesValidationService;
    private LoggerInterface $logger;

    public function __construct(
        LanguagesRepository $languagesRepository,
        LoggerInterface $logger,
        LanguagesValidationService $languagesValidationService
    ) {
        $this->languagesRepository = $languagesRepository;
        $this->languagesValidationService = $languagesValidationService;
        $this->logger = $logger;
    }

    /**
     * Получение всех языков.
     */
    public function getAllLanguages(): array
    {
        try {
            $this->logger->info("Fetching all languages directly from repository.");
            $languages = $this->languagesRepository->findAllLanguages();

            if (empty($languages)) {
                $this->logger->info("No languages found in the database.");
                return [];
            }

            return array_map(
                fn($language) => $this->languagesValidationService->formatLanguageData($language),
                $languages
            );
        } catch (\Exception $e) {
            $this->logger->error("Failed to fetch all languages: " . $e->getMessage());
            throw new \RuntimeException("Unable to fetch all languages.");
        }
    }

    /**
     * Получение языка по ID.
     */
    public function getLanguageById(int $languageId): array
    {
        try {
            $this->logger->info("Fetching language with ID {$languageId} directly from repository.");
            $language = $this->languagesValidationService->validateLanguageID($languageId);

            return $this->languagesValidationService->formatLanguageData($language);
        } catch (\InvalidArgumentException $e) {
            $this->logger->warning("Validation error for Language ID {$languageId}: " . $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error("Failed to fetch language with ID {$languageId}: " . $e->getMessage());
            throw new \RuntimeException("Unable to fetch language with ID {$languageId}.");
        }
    }

    /**
     * Валидация существования языка по ID.
     */
    public function validateLanguageID(mixed $languageId): array
    {
        try {
            $this->logger->info("Validating Language ID: {$languageId}");
            $language = $this->languagesValidationService->validateLanguageID($languageId);

            $formattedLanguage = $this->languagesValidationService->formatLanguageData($language);

            if (!isset($formattedLanguage['Language']['LanguageID'])) {
                throw new \InvalidArgumentException("Language data does not contain 'LanguageID'.");
            }

            return $formattedLanguage['Language'];
        } catch (\InvalidArgumentException $e) {
            $this->logger->warning("Validation failed for Language ID {$languageId}: {$e->getMessage()}");
            throw $e;
        }
    }


    /**
     * Проверка неизменяемости LanguageID.
     */
    public function checkImmutableLanguageID(array $data, int $currentLanguageId): void
    {
        try {
            $this->logger->info("Checking immutability of LanguageID for Translation.");
            $this->languagesValidationService->checkImmutableLanguageID($data, $currentLanguageId);
        } catch (\InvalidArgumentException $e) {
            $this->logger->warning("Immutable check failed for LanguageID: " . $e->getMessage());
            throw $e;
        }
    }
}
