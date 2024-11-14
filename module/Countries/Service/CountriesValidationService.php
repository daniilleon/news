<?php

namespace Module\Countries\Service;

use Module\Countries\Entity\Countries;
use Module\Countries\Entity\CountryTranslation;
use Module\Countries\Repository\CountriesRepository;
use Module\Countries\Repository\CountryTranslationRepository;
use Module\Languages\Entity\Language;
use Psr\Log\LoggerInterface;

class CountriesValidationService
{
    private CountriesRepository $countryRepository;
    private CountryTranslationRepository $translationRepository;
    private LoggerInterface $logger;

    public function __construct(
        CountriesRepository $countryRepository,
        CountryTranslationRepository $translationRepository,
        LoggerInterface $logger
    ) {
        $this->countryRepository = $countryRepository;
        $this->translationRepository = $translationRepository;
        $this->logger = $logger;
    }

    /**
     * Валидация поля CountryLink.
     *
     * @param array $data
     * @throws \InvalidArgumentException если данные не валидны
     */
    public function validateCountryLink(array $data): void
    {
        if (!empty($data['CountryLink']) &&!preg_match('/^[a-zA-Z0-9_-]+$/', $data['CountryLink'])) {
            $this->logger->error("Invalid characters in CountryLink.");
            throw new \InvalidArgumentException("Field 'CountryLink' can contain only letters, numbers, underscores, and hyphens.");
        }
    }

    /**
     * Валидация данных перевода категории.
     */
    public function validateCountryTranslationData(array $data): void
    {
        // Проверка допустимых символов и длины для CountryName, если он передан
        if (isset($data['CountryName'])) {
            $countryName = $data['CountryName'];

            // Проверка на допустимые символы и длину
            if (!preg_match('/^[\p{L}0-9 _-]{1,20}$/u', $countryName)) {
                $this->logger->error("Invalid characters or length in CountryName.");
                throw new \InvalidArgumentException("Field 'CountryName' can contain only letters, numbers, underscores, hyphens, spaces, and must be no more than 20 characters long.");
            }

            // Проверка, что CountryName не состоит только из цифр
            if (preg_match('/^\d+$/', $countryName)) {
                $this->logger->error("CountryName cannot consist only of numbers.");
                throw new \InvalidArgumentException("Field 'CountryName' cannot consist only of numbers.");
            }
        }

        // Если нужна проверка других полей, добавляем их сюда
        if (isset($data['CountryDescription']) && strlen($data['CountryDescription']) > 500) { // пример ограничения
            $this->logger->error("CountryDescription is too long.");
            throw new \InvalidArgumentException("Field 'CountryDescription' cannot exceed 500 characters.");
        }
    }

    /**
     * Проверка на уникальность CountryLink.
     *
     * @param string $countryLink
     * @param int|null $excludeId ID категории для исключения (например, при обновлении)
     * @throws \InvalidArgumentException если такой CountryLink уже существует
     */
    public function ensureUniqueCountryLink(?string $countryLink, ?int $excludeId = null): void
    {
        // Проверка, что CountryLink передан
        if (empty($countryLink)) {
            $this->logger->error("Field 'CountryLink' is required.");
            throw new \InvalidArgumentException("Field 'CountryLink' is required.");
        }

        // Проверка на уникальность CountryLink
        $existingCountry = $this->countryRepository->findCountryByLink($countryLink);
        if ($existingCountry && ($excludeId === null || $existingCountry->getCountryID() !== $excludeId)) {
            $this->logger->error("Duplicate CountryLink found: " . $countryLink);
            throw new \InvalidArgumentException("CountryLink '{$countryLink}' already exists.");
        }
    }

    /**
     * Проверка на уникальность CountryName.
     *
     * @param string array $data
     * @param int|null $excludeId ID категории для исключения (например, при обновлении)
     * @throws \InvalidArgumentException если такой CountryName уже существует
     */
    public function ensureUniqueCountryName(?string $countryName, ?int $excludeId = null): void
    {
        // Проверка обязательности поля CountryName
        if (empty($countryName)) {
            $this->logger->error("CountryName is required.");
            throw new \InvalidArgumentException("Field 'CountryName' is required and cannot be empty.");
        }

    }

    /**
     * Получение и проверка существования категории по ID.
     */
    public function validateCountryExists(mixed $countryID): Countries
    {
        // Проверка на наличие CountryID
        if ($countryID === null) {
            $this->logger->error("Field 'CountryID' is required.");
            throw new \InvalidArgumentException("Field 'CountryID' is required.");
        }

        // Проверка на целочисленный тип ID
        if (!is_int($countryID)) {
            $this->logger->error("Field 'CountryID' must be an integer.");
            throw new \InvalidArgumentException("Field 'CountryID' must be an integer.");
        }

        $country = $this->countryRepository->findCountryById($countryID);
        if (!$country) {
            $this->logger->warning("Country with ID $countryID not found.");
            throw new \InvalidArgumentException("Country with ID $countryID not found.");
        }
        return $country;
    }

    /**
     * Получение и проверка уникальности перевода.
     */
    public function ensureUniqueTranslation(Countries $country, Language $language): void
    {
        $existingTranslation = $this->translationRepository->findTranslationByCountryAndLanguage($country, $language);
        if ($existingTranslation) {
            $this->logger->error("Translation for Country ID {$country->getCountryID()} with Language ID {$language->getLanguageID()} already exists.");
            throw new \InvalidArgumentException("Translation for this language already exists for this country.");
        }
    }

    /**
     * Форматирование данных категории для ответа.
     *
     * @param Countries $country
     * @return array
     */
    public function formatCountryData(Countries $country, bool $detail = false): array
    {
        if($detail) {
            return [
                'Countries' => [
                    'CountryID' => $country->getCountryID(),
                    'CountryLink' => $country->getCountryLink(),
                ]
            ];
        } else {
            return [
                'CountryID' => $country->getCountryID(),
                'CountryLink' => $country->getCountryLink(),
            ];
        }
    }

    /**
     * Форматирование данных перевода категории для ответа.
     *
     * @param CountryTranslation $translation
     * @return array
     */
    public function formatCountryTranslationData(CountryTranslation $translation): array
    {
        return [
            'TranslationID' => $translation->getCountryTranslationID(),
            'LanguageID' => $translation->getLanguageID()->getLanguageID(),
            'CountryName' => $translation->getCountryName(),
            'CountryDescription' => $translation->getCountryDescription(),
        ];
    }

}
