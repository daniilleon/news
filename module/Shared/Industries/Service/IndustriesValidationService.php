<?php

namespace Module\Shared\Industries\Service;

use Module\Shared\Industries\Entity\Industries;
use Module\Shared\Industries\Entity\IndustryTranslations;
use Module\Shared\Industries\Repository\IndustriesRepository;
use Module\Shared\Industries\Repository\IndustryTranslationsRepository;
use Module\Common\Service\LanguagesProxyService;
use Psr\Log\LoggerInterface;

class IndustriesValidationService
{
    private IndustriesRepository $industryRepository;
    private IndustryTranslationsRepository $translationRepository;
    private LanguagesProxyService $languagesProxyService;
    private LoggerInterface $logger;

    public function __construct(
        IndustriesRepository           $industryRepository,
        IndustryTranslationsRepository $translationRepository,
        LanguagesProxyService $languagesProxyService,
        LoggerInterface                $logger
    ) {
        $this->industryRepository = $industryRepository;
        $this->translationRepository = $translationRepository;
        $this->languagesProxyService = $languagesProxyService;
        $this->logger = $logger;
    }

    /**
     * Валидация поля IndustryLink.
     *
     * @param array $data
     * @throws \InvalidArgumentException если данные не валидны
     */
    public function validateIndustryLink(array $data): void
    {
        if (!empty($data['IndustryLink']) &&!preg_match('/^[a-zA-Z0-9_-]+$/', $data['IndustryLink'])) {
            $this->logger->error("Invalid characters in IndustryLink.");
            throw new \InvalidArgumentException("Field 'IndustryLink' can contain only letters, numbers, underscores, and hyphens.");
        }
    }

    /**
     * Валидация данных перевода категории.
     */
    public function validateIndustryTranslationData(array $data): void
    {
        // Проверка допустимых символов и длины для IndustryName, если он передан
        if (isset($data['IndustryName'])) {
            $industryName = $data['IndustryName'];

            // Проверка на допустимые символы и длину
            if (!preg_match('/^[\p{L}0-9 _-]{1,20}$/u', $industryName)) {
                $this->logger->error("Invalid characters or length in IndustryName.");
                throw new \InvalidArgumentException("Field 'IndustryName' can contain only letters, numbers, underscores, hyphens, spaces, and must be no more than 20 characters long.");
            }

            // Проверка, что IndustryName не состоит только из цифр
            if (preg_match('/^\d+$/', $industryName)) {
                $this->logger->error("IndustryName cannot consist only of numbers.");
                throw new \InvalidArgumentException("Field 'IndustryName' cannot consist only of numbers.");
            }
        }
        // Если нужна проверка других полей, добавляем их сюда
        if (isset($data['IndustryDescription']) && strlen($data['IndustryDescription']) > 500) { // пример ограничения
            $this->logger->error("IndustryDescription is too long.");
            throw new \InvalidArgumentException("Field 'IndustryDescription' cannot exceed 500 characters.");
        }
    }

    /**
     * Проверка на уникальность IndustryLink.
     *
     * @param string $industryLink
     * @param int|null $excludeId ID категории для исключения (например, при обновлении)
     * @throws \InvalidArgumentException если такой IndustryLink уже существует
     */
    public function ensureUniqueIndustryLink(?string $industryLink, ?int $excludeId = null): void
    {
        // Проверка, что IndustryLink передан
        if (empty($industryLink)) {
            $this->logger->error("Field 'IndustryLink' is required.");
            throw new \InvalidArgumentException("Field 'IndustryLink' is required.");
        }

        // Проверка на уникальность IndustryLink
        $existingIndustry = $this->industryRepository->findIndustryByLink($industryLink);
        if ($existingIndustry && ($excludeId === null || $existingIndustry->getIndustryID() !== $excludeId)) {
            $this->logger->error("Duplicate IndustryLink found: " . $industryLink);
            throw new \InvalidArgumentException("IndustryLink '{$industryLink}' already exists.");
        }
    }

    /**
     * Проверка на уникальность IndustryName.
     *
     * @param string array $data
     * @param int|null $excludeId ID категории для исключения (например, при обновлении)
     * @throws \InvalidArgumentException если такой IndustryLink уже существует
     */
    public function ensureUniqueIndustryName(?string $industryName, ?int $excludeId = null): void
    {
        // Проверка обязательности поля IndustryName
        if (empty($industryName)) {
            $this->logger->error("IndustryName is required.");
            throw new \InvalidArgumentException("Field 'IndustryName' is required and cannot be empty.");
        }

    }

    /**
     * Получение и проверка существования категории по ID.
     */
    public function validateIndustryExists(mixed $industryID): Industries
    {
        // Проверка на наличие IndustryID
        if ($industryID === null) {
            $this->logger->error("Field 'IndustryID' is required.");
            throw new \InvalidArgumentException("Field 'IndustryID' is required.");
        }

        // Проверка на целочисленный тип ID
        if (!is_int($industryID)) {
            $this->logger->error("Field 'IndustryID' must be an integer.");
            throw new \InvalidArgumentException("Field 'IndustryID' must be an integer.");
        }

        $industry = $this->industryRepository->findIndustryById($industryID);
        if (!$industry) {
            $this->logger->warning("Industry with ID $industryID not found.");
            throw new \InvalidArgumentException("Industry with ID $industryID not found.");
        }
        return $industry;
    }

    /**
     * Получение и проверка уникальности перевода.
     */
    public function ensureUniqueTranslation(Industries $industry, int $languageId): void
    {
        // Валидация языка через прокси
        $this->languagesProxyService->validateLanguageID($languageId);
        $existingTranslation = $this->translationRepository->findTranslationByIndustryAndLanguage($industry, $languageId);

        if ($existingTranslation) {
            $this->logger->error("Translation for Industry ID {$industry->getIndustryID()} with Language ID {$languageId} already exists.");
            throw new \InvalidArgumentException("Translation for this language already exists for this Industry.");
        }
    }

    /**
     * Форматирование данных категории для ответа.
     *
     * @param Industries $industry
     * @return array
     */
    public function formatIndustryData(Industries $industry, bool $detail = false, ?int $languageId = null): array
    {
        $industryData = [
            'IndustryID' => $industry->getIndustryID(),
            'IndustryLink' => $industry->getIndustryLink(),
            'OgImage' => $industry->getOgImage(),
        ];

        // Если требуется детальная информация и указан язык, получаем перевод
        if ($detail && $languageId) {
            try {
            $this->languagesProxyService->getLanguageById($languageId);
            $translation = $this->getIndustryTranslations($industry, $languageId);

                $industryData['Translation'] = $translation
                    ? $this->formatIndustryTranslationsData($translation)
                    : 'Translation not available for the selected language.';

            } catch (\Exception $e) {
                $this->logger->warning("Failed to fetch translation for Industry ID {$industry->getIndustryID()}: " . $e->getMessage());
                    $industryData['Translation'] = 'Language details unavailable.';
                }
        }
        return $detail ? ['Industries' => $industryData] : $industryData;
    }


    /**
     * Форматирование данных перевода категории для ответа.
     *
     * @param IndustryTranslations $translation
     * @return array
     */
    public function formatIndustryTranslationsData(IndustryTranslations $translation): array
    {
        return [
            'IndustryTranslationID' => $translation->getIndustryTranslationID(),
            'LanguageID' => $translation->getLanguageID(),
            'IndustryName' => $translation->getIndustryName(),
            'IndustryDescription' => $translation->getIndustryDescription(),
        ];
    }

    public function getIndustryTranslations(Industries $industry, int $languageId): ?IndustryTranslations
    {
        $translation = $this->translationRepository->findTranslationByIndustryAndLanguage($industry, $languageId);

        if (!$translation) {
            $this->logger->info("No translation found for Industry ID {$industry->getIndustryID()} and language ID {$languageId}.");
        } else {
            $this->logger->info("Translation found: " . json_encode([
                    'IndustryID' => $industry->getIndustryID(),
                    'LanguageID' => $languageId,
                    'TranslationID' => $translation->getIndustryTranslationID(),
                ]));
        }

        return $translation;
    }

}
