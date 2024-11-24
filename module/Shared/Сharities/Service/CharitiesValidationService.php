<?php

namespace Module\Shared\Charities\Service;

use Module\Shared\Charities\Entity\Charities;
use Module\Shared\Charities\Entity\CharityTranslations;
use Module\Shared\Charities\Repository\CharitiesRepository;
use Module\Shared\Charities\Repository\CharityTranslationsRepository;
use Module\Common\Proxy\Core\LanguagesProxyService;
use Psr\Log\LoggerInterface;

class CharitiesValidationService
{
    private CharitiesRepository $charityRepository;
    private CharityTranslationsRepository $translationRepository;
    private LanguagesProxyService $languagesProxyService;
    private LoggerInterface $logger;

    public function __construct(
        CharitiesRepository           $charityRepository,
        CharityTranslationsRepository $translationRepository,
        LanguagesProxyService $languagesProxyService,
        LoggerInterface                $logger
    ) {
        $this->charityRepository = $charityRepository;
        $this->translationRepository = $translationRepository;
        $this->languagesProxyService = $languagesProxyService;
        $this->logger = $logger;
    }

    /**
     * Валидация поля CharityLink.
     *
     * @param array $data
     * @throws \InvalidArgumentException если данные не валидны
     */
    public function validateCharityLink(array $data): void
    {
        if (!empty($data['CharityLink']) &&!preg_match('/^[a-zA-Z0-9_-]+$/', $data['CharityLink'])) {
            $this->logger->error("Invalid characters in CharityLink.");
            throw new \InvalidArgumentException("Field 'CharityLink' can contain only letters, numbers, underscores, and hyphens.");
        }
    }

    /**
     * Валидация данных перевода категории.
     */
    public function validateCharityTranslationData(array $data): void
    {
        // Проверка допустимых символов и длины для CharityName, если он передан
        if (isset($data['CharityName'])) {
            $charityName = $data['CharityName'];

            // Проверка на допустимые символы и длину
            if (!preg_match('/^[\p{L}0-9 _-]{1,20}$/u', $charityName)) {
                $this->logger->error("Invalid characters or length in CharityName.");
                throw new \InvalidArgumentException("Field 'CharityName' can contain only letters, numbers, underscores, hyphens, spaces, and must be no more than 20 characters long.");
            }

            // Проверка, что CharityName не состоит только из цифр
            if (preg_match('/^\d+$/', $charityName)) {
                $this->logger->error("CharityName cannot consist only of numbers.");
                throw new \InvalidArgumentException("Field 'CharityName' cannot consist only of numbers.");
            }
        }
        // Если нужна проверка других полей, добавляем их сюда
        if (isset($data['CharityDescription']) && strlen($data['CharityDescription']) > 500) { // пример ограничения
            $this->logger->error("CharityDescription is too long.");
            throw new \InvalidArgumentException("Field 'CharityDescription' cannot exceed 500 characters.");
        }
    }

    /**
     * Проверка на уникальность CharityLink.
     *
     * @param string $charityLink
     * @param int|null $excludeId ID категории для исключения (например, при обновлении)
     * @throws \InvalidArgumentException если такой CharityLink уже существует
     */
    public function ensureUniqueCharityLink(?string $charityLink, ?int $excludeId = null): void
    {
        // Проверка, что CharityLink передан
        if (empty($charityLink)) {
            $this->logger->error("Field 'CharityLink' is required.");
            throw new \InvalidArgumentException("Field 'CharityLink' is required.");
        }

        // Проверка на уникальность CharityLink
        $existingCharity = $this->charityRepository->findCharityByLink($charityLink);
        if ($existingCharity && ($excludeId === null || $existingCharity->getCharityID() !== $excludeId)) {
            $this->logger->error("Duplicate CharityLink found: " . $charityLink);
            throw new \InvalidArgumentException("CharityLink '{$charityLink}' already exists.");
        }
    }

    /**
     * Проверка на уникальность CharityName.
     *
     * @param string array $data
     * @param int|null $excludeId ID категории для исключения (например, при обновлении)
     * @throws \InvalidArgumentException если такой CharityLink уже существует
     */
    public function ensureUniqueCharityName(?string $charityName, ?int $excludeId = null): void
    {
        // Проверка обязательности поля CharityName
        if (empty($charityName)) {
            $this->logger->error("CharityName is required.");
            throw new \InvalidArgumentException("Field 'CharityName' is required and cannot be empty.");
        }

    }

    /**
     * Получение и проверка существования категории по ID.
     */
    public function validateCharityExists(mixed $charityID): Charities
    {
        // Проверка на наличие CharityID
        if ($charityID === null) {
            $this->logger->error("Field 'CharityID' is required.");
            throw new \InvalidArgumentException("Field 'CharityID' is required.");
        }

        // Проверка на целочисленный тип ID
        if (!is_int($charityID)) {
            $this->logger->error("Field 'CharityID' must be an integer.");
            throw new \InvalidArgumentException("Field 'CharityID' must be an integer.");
        }

        $charity = $this->charityRepository->findCharityById($charityID);
        if (!$charity) {
            $this->logger->warning("Charity with ID $charityID not found.");
            throw new \InvalidArgumentException("Charity with ID $charityID not found.");
        }
        return $charity;
    }

    /**
     * Получение и проверка уникальности перевода.
     */
    public function ensureUniqueTranslation(Charities $charity, int $languageId): void
    {
        // Валидация языка через прокси
        $this->languagesProxyService->validateLanguageID($languageId);
        $existingTranslation = $this->translationRepository->findTranslationByCharityAndLanguage($charity, $languageId);

        if ($existingTranslation) {
            $this->logger->error("Translation for Charity ID {$charity->getCharityID()} with Language ID {$languageId} already exists.");
            throw new \InvalidArgumentException("Translation for this language already exists for this Charity.");
        }
    }

    /**
     * Форматирование данных категории для ответа.
     *
     * @param Charities $charity
     * @return array
     */
    public function formatCharityData(Charities $charity, bool $detail = false, ?int $languageId = null): array
    {
        $charityData = [
            'CharityID' => $charity->getCharityID(),
            'CharityLink' => $charity->getCharityLink(),
            'OgImage' => $charity->getOgImage(),
        ];

        // Если требуется детальная информация и указан язык, получаем перевод
        if ($detail && $languageId) {
            try {
            $this->languagesProxyService->getLanguageById($languageId);
            $translation = $this->getCharityTranslations($charity, $languageId);

                $charityData['Translation'] = $translation
                    ? $this->formatCharityTranslationsData($translation)
                    : 'Translation not available for the selected language.';

            } catch (\Exception $e) {
                $this->logger->warning("Failed to fetch translation for Charity ID {$charity->getCharityID()}: " . $e->getMessage());
                    $charityData['Translation'] = 'Language details unavailable.';
                }
        }
        return $detail ? ['Charities' => $charityData] : $charityData;
    }


    /**
     * Форматирование данных перевода категории для ответа.
     *
     * @param CharityTranslations $translation
     * @return array
     */
    public function formatCharityTranslationsData(CharityTranslations $translation): array
    {
        return [
            'CharityTranslationID' => $translation->getCharityTranslationID(),
            'LanguageID' => $translation->getLanguageID(),
            'CharityName' => $translation->getCharityName(),
            'CharityDescription' => $translation->getCharityDescription(),
        ];
    }

    public function getCharityTranslations(Charities $charity, int $languageId): ?CharityTranslations
    {
        $translation = $this->translationRepository->findTranslationByCharityAndLanguage($charity, $languageId);

        if (!$translation) {
            $this->logger->info("No translation found for Charity ID {$charity->getCharityID()} and language ID {$languageId}.");
        } else {
            $this->logger->info("Translation found: " . json_encode([
                    'CharityID' => $charity->getCharityID(),
                    'LanguageID' => $languageId,
                    'TranslationID' => $translation->getCharityTranslationID(),
                ]));
        }

        return $translation;
    }

}
