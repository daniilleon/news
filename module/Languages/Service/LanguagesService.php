<?php

namespace Module\Languages\Service;

use Exception;
use Module\Languages\Repository\LanguagesRepository;
use Module\Languages\Entity\Language;
use Module\Common\Service\LanguagesValidationService;
use Psr\Log\LoggerInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

class LanguagesService
{
    private LanguagesRepository $languageRepository;
    private LanguagesValidationService $languagesValidationService;
    private LoggerInterface $logger;

    public function __construct(
        LanguagesRepository $languageRepository,
        LanguagesValidationService $languagesValidationService,
        LoggerInterface $logger
    ) {
        $this->languageRepository = $languageRepository;
        $this->languagesValidationService = $languagesValidationService;
        $this->logger = $logger;
        $this->logger->info("LanguagesService instance created.");
    }


    // Добавление языка по умолчанию
    public function addDefaultLanguage(): array
    {
        $this->logger->info("Adding default language: 'EN' - 'English'.");
        return $this->addLanguage(['LanguageCode'=>'ru', 'LanguageName'=>'Русский']);
    }

    /**
     * Получение всех языков.
     *
     * @return array
     */
    public function getAllLanguages(): array
    {
        $this->logger->info("Executing getAllLanguages method.");
        try {
            $languages = $this->languageRepository->findAllLanguages();

            // Проверка, есть ли языки
            if (empty($languages)) {
                $this->logger->info("No languages found in the database.");
                return [
                    'message' => 'No languages found in the database.'
                ];
            }
            // Возвращаем список языков, если они существуют
            return [
                'languages' => array_map([$this->languagesValidationService, 'formatLanguageData'], $languages)
            ];
        } catch (\InvalidArgumentException $e) {
            $this->logger->error("Validation error while fetching languages: " . $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error("Unable to fetch languages: " . $e->getMessage());
            throw new \RuntimeException("Unable to fetch languages at the moment.", 0, $e);
        }
    }

    // Получение языка по ID
    public function getLanguageById(int $id): array
    {
        $this->logger->info("Executing getLanguageById method for ID: $id");
        try {
            $language = $this->languageRepository->findLanguageById($id);

            if (!$language) {
                $this->logger->info("Language with ID $id not found.");
                throw new \InvalidArgumentException("Language with ID $id not found.");
            }
            return $this->languagesValidationService->formatLanguageData($language);
        } catch (\InvalidArgumentException $e) {
            $this->logger->error("Validation error while fetching language by ID: " . $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error("Unable to fetch language by ID: " . $e->getMessage());
            throw new \RuntimeException("Unable to fetch language at the moment.", 0, $e);
        }
    }


    // Добавление нового языка
    public function addLanguage(array $data): array
    {
        $this->logger->info("Executing addLanguage method.");

        $languageCode = $data['LanguageCode'] ?? null;
        $languageName = $data['LanguageName'] ?? null;

        if (!$languageCode || !$languageName) {
            $this->logger->warning("Both LanguageCode and LanguageName are required.");
            throw new \InvalidArgumentException("LanguageCode and LanguageName are required.");
        }

        try {
            $this->validateLanguageData($languageCode, $languageName);

            if ($this->languageRepository->findOneBy(['LanguageCode' => $languageCode])) {
                $this->logger->warning("Language with code '$languageCode' already exists.");
                throw new \InvalidArgumentException("Language with code '$languageCode' already exists.");
            }

            $language = (new Language())
                ->setLanguageCode(strtoupper($languageCode))
                ->setLanguageName($languageName);

            $this->languageRepository->saveLanguage($language, true);
            $this->logger->info("Language '$languageName' with code '$languageCode' successfully added.");

            return [
                'message' => 'Language added successfully.',
                'language' => $this->languagesValidationService->formatLanguageData($language)
            ];
        } catch (\InvalidArgumentException $e) {
            $this->logger->warning("Validation error: " . $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error("Unable to add language: " . $e->getMessage());
            throw new \RuntimeException("An unexpected error occurred while adding the language.");
        }
    }


    // Обновление языка по его ID
    public function updateLanguage(int $id, array $data): array
    {
        $this->logger->info("Executing updateLanguage method for ID: $id");
        try {
            $language = $this->languageRepository->findLanguageById($id);

            if (!$language) {
                $this->logger->warning("Language with ID $id not found.");
                throw new \InvalidArgumentException("Language with ID $id not found.");
            }

            $languageCode = $data['LanguageCode'] ?? $language->getLanguageCode();
            $languageName = $data['LanguageName'] ?? $language->getLanguageName();

            // Валидация данных
            $this->validateLanguageData($languageCode, $languageName);

            // Установка новых значений
            $language->setLanguageCode(strtoupper($languageCode))
                ->setLanguageName($languageName);

            $this->languageRepository->saveLanguage($language, true);
            $this->logger->info("Language with ID $id successfully updated.");

            return [
                'language' => $this->languagesValidationService->formatLanguageData($language),
                'message' => 'Language updated successfully.'
            ];
        } catch (UniqueConstraintViolationException $e) {
            $this->logger->warning("Duplicate LanguageCode error: Language code '$languageCode' already exists.");
            throw new \InvalidArgumentException("Language with this code already exists.");
        } catch (\InvalidArgumentException $e) {
            $this->logger->warning("Validation error: " . $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error("Unable to update language: " . $e->getMessage());
            throw new \RuntimeException("An unexpected error occurred while updating the language.");
        }
    }

    // Удаление языка по его ID
    public function deleteLanguage(int $id): array
    {
        $this->logger->info("Executing deleteLanguage method for ID: $id");
        try {
            $language = $this->languageRepository->findLanguageById($id);

            if (!$language) {
                $this->logger->warning("Language with ID $id not found.");
                throw new \InvalidArgumentException("Language with ID $id not found.");
            }

            $this->languageRepository->delete($language, true);
            $this->logger->info("Language with ID $id successfully deleted.");

            return [
                'message' => "Language with ID $id successfully deleted."
            ];
        } catch (\Exception $e) {
            $this->logger->error("Unable to delete language: " . $e->getMessage());
            throw new \RuntimeException("An unexpected error occurred while deleting the language.");
        }
    }

    // Валидация данных языка
    private function validateLanguageData(string $languageCode, string $languageName): void
    {
        $this->languagesValidationService->validateLanguageCode($languageCode);
        $this->languagesValidationService->validateLanguageName($languageName);
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