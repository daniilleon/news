<?php
namespace Module\Languages\Service;

use Module\Languages\Repository\LanguageRepository;
use Module\Languages\Entity\Language;
use Psr\Log\LoggerInterface;

class LanguageService
{
    private LanguageRepository $languageRepository;
    private LoggerInterface $logger;

    public function __construct(LanguageRepository $languageRepository, LoggerInterface $logger)
    {
        $this->languageRepository = $languageRepository;
        $this->logger = $logger;
        $this->logger->info("LanguageService instance created.");
    }

    // Валидация данных перед обработкой
    private function validateLanguageData(string $code, string $name): void
    {
        // Validate 'code' field
        if (empty(trim($code))) {
            throw new \InvalidArgumentException("Field 'code' cannot be empty or only spaces.");
        }

        if (strlen($code) > 3) {
            throw new \InvalidArgumentException("Field 'code' cannot contain more than 3 characters.");
        }

        if (!preg_match('/^[A-Z]{1,3}$/', strtoupper($code))) {
            throw new \InvalidArgumentException("Field 'code' must contain only uppercase Latin letters.");
        }

        // Convert code to uppercase before saving
        $code = strtoupper($code);

        // Validate 'name' field
        if (empty(trim($name))) {
            throw new \InvalidArgumentException("Field 'name' cannot be empty or only spaces.");
        }

        if (strlen($name) > 15) {
            throw new \InvalidArgumentException("Field 'name' cannot contain more than 15 characters.");
        }

        //if (!preg_match('/^[A-Za-z]+$/', $name)) {
        if (!preg_match('/^[A-Za-zА-Яа-я]+$/u', $name)) {
            throw new \InvalidArgumentException("Field 'name' must contain only letters without spaces.");
            //throw new \InvalidArgumentException("Field 'name' must contain only Latin letters without spaces.");
        }
    }


    // Получение всех языков с логированием
    public function getAllLanguages(): array
    {
        $this->logger->info("Executing getAllLanguages method.");

        $languages = $this->languageRepository->findAllLanguages();

        if (empty($languages)) {
            $this->logger->info("No languages found in database.");
        }

        return $languages;
    }

    // Добавление нового языка с логированием
    public function addLanguage(string $code, string $name): Language
    {
        $this->logger->info("Executing addLanguage method in LanguageService.", ['code' => $code, 'name' => $name]);

        // Выполняем валидацию
        $errors = $this->validateLanguageData($code, $name);
        if (!empty($errors)) {
            $errorMessage = implode("; ", $errors);
            $this->logger->warning("Validation failed: " . $errorMessage);
            throw new \InvalidArgumentException($errorMessage);
        }

        // Преобразуем код в верхний регистр
        $code = strtoupper($code);

        try {
            $existingLanguage = $this->languageRepository->findOneBy(['code' => $code]);

            if ($existingLanguage) {
                $this->logger->warning("Language with code '{$code}' already exists.");
                throw new \InvalidArgumentException("Language with code '{$code}' already exists.");
            }

            $language = new Language();
            $language->setCode($code)->setName($name);

            $this->languageRepository->save($language, true);
            $this->logger->info("Language '{$name}' with code '{$code}' successfully added.");
            return $language;
        } catch (\InvalidArgumentException $e) {
            // Бросаем ошибку дальше для обработки в контроллере
            throw $e;
        } catch (\Exception $e) {
            // Обработка общих ошибок
            $this->logger->error("Error in addLanguage method: " . $e->getMessage(), ['exception' => $e]);
            throw new \RuntimeException("An unexpected error occurred while adding the language. Please try again later.");
        }
    }


    // Получение языка по ID с логированием
    public function getLanguageById(int $id): ?Language
    {
        $this->logger->info("Executing getLanguageById method for ID: {$id}");

        try {
            $language = $this->languageRepository->find($id);

            if (!$language) {
                $this->logger->info("Language with ID {$id} not found.");
                return null;
            }

            return $language;
        } catch (\Exception $e) {
            $this->logger->error("Error in getLanguageById method: " . $e->getMessage(), ['exception' => $e]);
            return null;
        }
    }

    // Удаление языка по ID с логированием
    public function deleteLanguage(int $id): bool
    {
        $this->logger->info("Executing deleteLanguage method for ID: $id");

        try {
            $language = $this->languageRepository->find($id);

            if (!$language) {
                $this->logger->warning("Language with ID $id not found for deletion.");
                return false;
            }

            // Используем EntityManager для удаления сущности
            $entityManager = $this->languageRepository->getEntityManager();
            $entityManager->remove($language);
            $entityManager->flush();

            $this->logger->info("Language with ID $id successfully deleted.");
            return true;
        } catch (\Exception $e) {
            $this->logger->error("Error in deleteLanguage method: " . $e->getMessage(), ['exception' => $e]);
            return false;
        }
    }

    // Обновление языка по ID с логированием и валидацией
    public function updateLanguage(int $id, array $data): ?Language
    {
        $this->logger->info("Executing updateLanguage method in LanguageService for ID: {$id}", ['data' => $data]);

        try {
            // Проверяем, есть ли в данных поля для обновления
            if (isset($data['code'])) {
                $data['code'] = strtoupper($data['code']);
                $this->validateLanguageData($data['code'], $data['name'] ?? '');
            }

            if (isset($data['name'])) {
                $this->validateLanguageData($data['code'] ?? '', $data['name']);
            }

            // Выполняем обновление через репозиторий
            $updatedLanguage = $this->languageRepository->updateLanguage($id, $data);

            if (!$updatedLanguage) {
                $this->logger->warning("Language with ID {$id} not found for update.");
                return null;
            }

            $this->logger->info("Language with ID {$id} successfully updated.", [
                'id' => $updatedLanguage->getId(),
                'code' => $updatedLanguage->getCode(),
                'name' => $updatedLanguage->getName()
            ]);

            return $updatedLanguage;

        } catch (\InvalidArgumentException $e) {
            $this->logger->warning("Validation error in updateLanguage method: " . $e->getMessage(), ['data' => $data]);
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error("Error in updateLanguage method: " . $e->getMessage(), ['exception' => $e]);
            throw new \RuntimeException("An unexpected error occurred while updating the language. Please try again later.");
        }
    }
}
