<?php

namespace Module\Languages\Service;

use Module\Languages\Entity\Language;
use Module\Languages\Repository\LanguagesRepository;
use Psr\Log\LoggerInterface;

class LanguagesValidationService
{
    private LanguagesRepository $languagesRepository;
    private LoggerInterface $logger;

    public function __construct(LanguagesRepository $languagesRepository, LoggerInterface $logger)
    {
        $this->languagesRepository = $languagesRepository;
        $this->logger = $logger;
    }

    /**
     * Валидация кода языка.
     *
     * @param string $languageCode
     * @return bool
     */
    public function validateLanguageCode(string $languageCode): void
    {
        if (empty(trim($languageCode))) {
            $this->logger->error("LanguageCode cannot be empty or only spaces.");
            throw new \InvalidArgumentException("LanguageCode cannot be empty or only spaces.");
        }

        if (strlen($languageCode) > 3) {
            $this->logger->error("LanguageCode cannot contain more than 3 characters.");
            throw new \InvalidArgumentException("LanguageCode cannot contain more than 3 characters.");
        }

        if (!preg_match('/^[A-Z]{1,3}$/', strtoupper($languageCode))) {
            $this->logger->error("LanguageCode must contain only uppercase Latin letters.");
            throw new \InvalidArgumentException("LanguageCode must contain only uppercase Latin letters.");
        }
    }

    /**
     * Валидация имени языка.
     *
     * @param string $languageName
     * @return bool
     */
    public function validateLanguageName(string $languageName): void
    {
        if (empty(trim($languageName))) {
            $this->logger->error("LanguageName cannot be empty or only spaces.");
            throw new \InvalidArgumentException("LanguageName cannot be empty or only spaces.");
        }

        if (strlen($languageName) > 20) {
            $this->logger->error("LanguageName cannot contain more than 20 characters.");
            throw new \InvalidArgumentException("LanguageName cannot contain more than 15 characters.");
        }

        if (!preg_match('/^[A-Za-zА-Яа-я]+$/u', $languageName)) {
            $this->logger->error("LanguageName must contain only letters without spaces.");
            throw new \InvalidArgumentException("LanguageName must contain only letters without spaces.");
        }
    }


    public function validateLanguageID(mixed $languageID): Language
    {
        // Проверка на наличие LanguageID
        if ($languageID === null) {
            $this->logger->error("Field 'LanguageID' is required.");
            throw new \InvalidArgumentException("Field 'LanguageID' is required.");
        }

        // Проверка на целочисленный тип ID
        if (!is_int($languageID)) {
            $this->logger->error("Field 'LanguageID' must be an integer.");
            throw new \InvalidArgumentException("Field 'LanguageID' must be an integer.");
        }

        // Основная проверка существования языка
        $language = $this->languagesRepository->find($languageID);
        if (!$language) {
            $this->logger->error("Language with ID {$languageID} not found.");
            throw new \InvalidArgumentException("Language with ID {$languageID} not found.");
        }
        return $language;
    }

    public function checkImmutableLanguageID(array $data, int $translationId): void
    {
        // Проверка на присутствие LanguageID в запросе
        if (isset($data['LanguageID'])) {
            $this->logger->error("LanguageID cannot be modified in translation update for translation ID $translationId.");
            throw new \InvalidArgumentException("LanguageID cannot be modified for an existing translation.");
        }
    }


    public function formatLanguageData(Language $language, bool $detailed = true): array
    {
        if ($detailed) {
            // Возвращаем полное описание языка
            return [
                'Language' => [
                    'LanguageID' => $language->getLanguageID(),
                    'LanguageCode' => $language->getLanguageCode(),
                    'LanguageName' => $language->getLanguageName(),
                ]
            ];
        } else {
            // Возвращаем только LanguageID как отдельное поле
            return ['LanguageID' => $language->getLanguageID()];
        }
    }

}
