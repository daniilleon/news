<?php

namespace Module\Languages\Service;

use Exception;
use Module\Languages\Repository\LanguagesRepository;
use Module\Languages\Entity\Language;
use Module\Common\Service\LanguagesValidationService;
use Psr\Log\LoggerInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use InvalidArgumentException;
use RuntimeException;

class LanguagesService
{
    private LanguagesRepository $languageRepository;
    private LanguagesValidationService $validationService;
    private LoggerInterface $logger;

    public function __construct(
        LanguagesRepository $languageRepository,
        LanguagesValidationService $validationService,
        LoggerInterface $logger
    ) {
        $this->languageRepository = $languageRepository;
        $this->validationService = $validationService;
        $this->logger = $logger;
        $this->logger->info("LanguagesService instance created.");
    }

    // Получение всех языков
    public function getAllLanguages(): array
    {
        $this->logger->info("Executing getAllLanguages method.");
        try {
            $languages = $this->languageRepository->findAllLanguages();

            if (empty($languages)) {
                $this->logger->info("No languages found in database.");
            }

            return array_map([$this, 'formatLanguageData'], $languages);
        } catch (Exception $e) {
            $this->logger->error("Unable to fetch languages: " . $e->getMessage());
            throw new RuntimeException("Unable to fetch languages at the moment.");
        }
    }

    // Получение языка по его ID
    public function getLanguageById(int $id): ?Language
    {
        $this->logger->info("Executing getLanguageById method for ID: $id");
        try {
            $language = $this->languageRepository->findLanguageById($id);

            if (!$language) {
                $this->logger->info("Language with ID $id not found.");
                return null;
            }

            return $language;
        } catch (Exception $e) {
            $this->logger->error("Unable to fetch language by ID: " . $e->getMessage());
            throw new RuntimeException("Unable to fetch the language at the moment.");
        }
    }

    // Добавление нового языка
    public function addLanguage(string $languageCode, string $languageName): Language
    {
        $this->logger->info("Executing addLanguage method.", ['LanguageCode' => $languageCode, 'LanguageName' => $languageName]);
        try {
            $this->validateLanguageData($languageCode, $languageName);

            if ($this->languageRepository->findOneBy(['LanguageCode' => $languageCode])) {
                $this->logger->warning("Language with code '$languageCode' already exists.");
                throw new InvalidArgumentException("Language with code '$languageCode' already exists.");
            }

            $language = (new Language())
                ->setLanguageCode(strtoupper($languageCode))
                ->setLanguageName($languageName);

            $this->languageRepository->saveLanguage($language, true);
            $this->logger->info("Language '$languageName' with code '$languageCode' successfully added.");

            return $language;
        } catch (InvalidArgumentException $e) {
            $this->logger->warning("Validation error: " . $e->getMessage());
            throw $e;
        } catch (Exception $e) {
            $this->logger->error("Unable to add language: " . $e->getMessage());
            throw new RuntimeException("An unexpected error occurred while adding the language.");
        }
    }

    // Обновление языка по его ID
    public function updateLanguage(int $id, array $data): ?Language
    {
        $this->logger->info("Executing updateLanguage method for ID: $id");
        try {
            $language = $this->languageRepository->findLanguageById($id);
            if (!$language) {
                $this->logger->warning("Language with ID $id not found.");
                return null;
            }

            $languageCode = $data['LanguageCode'] ?? $language->getLanguageCode();
            $languageName = $data['LanguageName'] ?? $language->getLanguageName();
            $this->validateLanguageData($languageCode, $languageName);

            $language->setLanguageCode(strtoupper($languageCode))
                ->setLanguageName($languageName);

            $this->languageRepository->saveLanguage($language, true);
            $this->logger->info("Language with ID $id successfully updated.");

            return $language;
        } catch (UniqueConstraintViolationException $e) {
            $this->logger->warning("Duplicate LanguageCode error: Language code '$languageCode' already exists.");
            throw new InvalidArgumentException("Language with this code already exists.");
        } catch (InvalidArgumentException $e) {
            $this->logger->warning("Validation error: " . $e->getMessage());
            throw $e;
        } catch (Exception $e) {
            $this->logger->error("Unable to update language: " . $e->getMessage());
            throw new RuntimeException("An unexpected error occurred while updating the language.");
        }
    }

    // Удаление языка по его ID
    public function deleteLanguage(int $id): bool
    {
        $this->logger->info("Executing deleteLanguage method for ID: $id");
        try {
            $language = $this->languageRepository->findLanguageById($id);
            if (!$language) {
                $this->logger->warning("Language with ID $id not found.");
                return false;
            }

            $this->languageRepository->delete($language, true);
            $this->logger->info("Language with ID $id successfully deleted.");

            return true;
        } catch (Exception $e) {
            $this->logger->error("Unable to delete language: " . $e->getMessage());
            throw new RuntimeException("An unexpected error occurred while deleting the language.");
        }
    }

    // Валидация данных языка
    private function validateLanguageData(string $languageCode, string $languageName): void
    {
        $this->validationService->validateLanguageCode($languageCode);
        $this->validationService->validateLanguageName($languageName);
    }

    // Форматирование данных языка для API
    public function formatLanguageData(Language $language): array
    {
        return [
            'LanguageID' => $language->getLanguageID(),
            'LanguageCode' => $language->getLanguageCode(),
            'LanguageName' => $language->getLanguageName(),
        ];
    }
}