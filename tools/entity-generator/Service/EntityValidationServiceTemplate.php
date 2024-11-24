<?php

namespace Module\{{ENTITY_DIR}}\{{ENTITY_NAME}}\Service;

use Module\{{ENTITY_DIR}}\{{ENTITY_NAME}}\Entity\{{ENTITY_NAME}};
use Module\{{ENTITY_DIR}}\{{ENTITY_NAME}}\Entity\{{ENTITY_NAME_ONE}}Translations;
use Module\{{ENTITY_DIR}}\{{ENTITY_NAME}}\Repository\{{ENTITY_NAME}}Repository;
use Module\{{ENTITY_DIR}}\{{ENTITY_NAME}}\Repository\{{ENTITY_NAME_ONE}}TranslationsRepository;
use Module\Common\Proxy\Core\LanguagesProxyService;
use Psr\Log\LoggerInterface;

class {{ENTITY_NAME}}ValidationService
{
    private {{ENTITY_NAME}}Repository ${{ENTITY_NAME_LOWER}}Repository;
    private {{ENTITY_NAME_ONE}}TranslationsRepository $translationRepository;
    private LanguagesProxyService $languagesProxyService;
    private LoggerInterface $logger;

    public function __construct(
        {{ENTITY_NAME}}Repository             ${{ENTITY_NAME_LOWER}}Repository,
        {{ENTITY_NAME_ONE}}TranslationsRepository $translationRepository,
        LanguagesProxyService                 $languagesProxyService,
        LoggerInterface                       $logger
    ) {
        $this->{{ENTITY_NAME_LOWER}}Repository = ${{ENTITY_NAME_LOWER}}Repository;
        $this->translationRepository = $translationRepository;
        $this->languagesProxyService = $languagesProxyService;
        $this->logger = $logger;
    }

    /**
     * Валидация поля {{ENTITY_NAME_ONE}}{{ENTITY_CODE_LINK}}.
     *
     * @param array $data
     * @throws \InvalidArgumentException если данные не валидны
     */
    public function validate{{ENTITY_NAME_ONE}}{{ENTITY_CODE_LINK}}(array $data): void
    {
        if (!empty($data['{{ENTITY_NAME_ONE}}{{ENTITY_CODE_LINK}}']) &&!preg_match('/^[a-zA-Z0-9_-]+$/', $data['{{ENTITY_NAME_ONE}}{{ENTITY_CODE_LINK}}'])) {
            $this->logger->error("Invalid characters in {{ENTITY_NAME_ONE}}{{ENTITY_CODE_LINK}}.");
            throw new \InvalidArgumentException("Field '{{ENTITY_NAME_ONE}}{{ENTITY_CODE_LINK}}' can contain only letters, numbers, underscores, and hyphens.");
        }
    }

    /**
     * Валидация данных перевода должности.
     */
    public function validate{{ENTITY_NAME_ONE}}TranslationData(array $data): void
    {
        // Проверка допустимых символов и длины для {{ENTITY_NAME_ONE}}Name, если он передан
        if (isset($data['{{ENTITY_NAME_ONE}}Name'])) {
            ${{ENTITY_NAME_LOWER}}Name = $data['{{ENTITY_NAME_ONE}}Name'];

            // Проверка на допустимые символы и длину
            if (!preg_match('#^[\p{L}0-9 _\-/]{1,50}$#u', ${{ENTITY_NAME_LOWER}}Name)) {
                $this->logger->error("Invalid characters or length in {{ENTITY_NAME_ONE}}Name.");
                throw new \InvalidArgumentException("Field '{{ENTITY_NAME_ONE}}Name' can contain only letters, numbers, underscores, hyphens, spaces, slashes, and must be no more than 50 characters long.");
            }

            // Проверка, что {{ENTITY_NAME_ONE}}Name не состоит только из цифр
            if (preg_match('/^\d+$/', ${{ENTITY_NAME_LOWER}}Name)) {
                $this->logger->error("{{ENTITY_NAME_ONE}}Name cannot consist only of numbers.");
                throw new \InvalidArgumentException("Field '{{ENTITY_NAME_ONE}}Name' cannot consist only of numbers.");
            }
        }
        // Если нужна проверка других полей, добавляем их сюда
        if (isset($data['{{ENTITY_NAME_ONE}}Description']) && strlen($data['{{ENTITY_NAME_ONE}}Description']) > 500) { // пример ограничения
            $this->logger->error("{{ENTITY_NAME_ONE}}Description is too long.");
            throw new \InvalidArgumentException("Field '{{ENTITY_NAME_ONE}}Description' cannot exceed 500 characters.");
        }
    }

    /**
     * Проверка на уникальность {{ENTITY_NAME_ONE}}{{ENTITY_CODE_LINK}}.
     *
     * @param string ${{ENTITY_NAME_LOWER}}{{ENTITY_CODE_LINK}}
     * @param int|null $excludeId ID должности для исключения (например, при обновлении)
     * @throws \InvalidArgumentException если такой {{ENTITY_NAME_ONE}}{{ENTITY_CODE_LINK}} уже существует
     */
    public function ensureUnique{{ENTITY_NAME_ONE}}{{ENTITY_CODE_LINK}}(?string ${{ENTITY_NAME_LOWER}}{{ENTITY_CODE_LINK}}, ?int $excludeId = null): void
    {
        // Проверка, что {{ENTITY_NAME_ONE}}{{ENTITY_CODE_LINK}} передан
        if (empty(${{ENTITY_NAME_LOWER}}{{ENTITY_CODE_LINK}})) {
            $this->logger->error("Field '{{ENTITY_NAME_ONE}}{{ENTITY_CODE_LINK}}' is required.");
            throw new \InvalidArgumentException("Field '{{ENTITY_NAME_ONE}}{{ENTITY_CODE_LINK}}' is required.");
        }

        // Проверка на уникальность {{ENTITY_NAME_ONE}}{{ENTITY_CODE_LINK}}
        $existing{{ENTITY_NAME_ONE}} = $this->{{ENTITY_NAME_LOWER}}Repository->find{{ENTITY_NAME_ONE}}By{{ENTITY_CODE_LINK}}(${{ENTITY_NAME_LOWER}}Code);
        if ($existing{{ENTITY_NAME_ONE}} && ($excludeId === null || $existing{{ENTITY_NAME_ONE}}->get{{ENTITY_NAME_ONE}}ID() !== $excludeId)) {
            $this->logger->error("Duplicate {{ENTITY_NAME_ONE}}{{ENTITY_CODE_LINK}} found: " . ${{ENTITY_NAME_LOWER}}{{ENTITY_CODE_LINK}});
            throw new \InvalidArgumentException("{{ENTITY_NAME_ONE}}{{ENTITY_CODE_LINK}} '{${{ENTITY_NAME_LOWER}}{{ENTITY_CODE_LINK}}}' already exists.");
        }
    }

    /**
     * Проверка на уникальность {{ENTITY_NAME_ONE}}Name.
     *
     * @param string ${{ENTITY_NAME_LOWER}}Name
     * @param int|null $excludeId ID категории для исключения (например, при обновлении)
     * @throws \InvalidArgumentException если такой {{ENTITY_NAME_ONE}}Name уже существует
     */
    public function ensureUnique{{ENTITY_NAME_ONE}}Name(?string ${{ENTITY_NAME_LOWER}}Name, ?int $excludeId = null): void
    {
        // Проверка обязательности поля {{ENTITY_NAME_ONE}}Name
        if (empty(${{ENTITY_NAME_LOWER}}Name)) {
            $this->logger->error("{{ENTITY_NAME_ONE}}Name is required.");
            throw new \InvalidArgumentException("Field '{{ENTITY_NAME_ONE}}Name' is required and cannot be empty.");
        }
    }

    /**
     * Получение и проверка существования должности по ID.
     */
    public function validate{{ENTITY_NAME_ONE}}Exists(mixed ${{ENTITY_NAME_LOWER}}ID): {{ENTITY_NAME}}
    {
        // Проверка на наличие ${{ENTITY_NAME_LOWER}}ID
        if (${{ENTITY_NAME_LOWER}}ID === null) {
            $this->logger->error("Field '{{ENTITY_NAME_ONE}}ID' is required.");
            throw new \InvalidArgumentException("Field '{{ENTITY_NAME_ONE}}ID' is required.");
        }

        // Проверка на целочисленный тип ID
        if (!is_int(${{ENTITY_NAME_LOWER}}ID)) {
            $this->logger->error("Field '{{ENTITY_NAME_ONE}}ID' must be an integer.");
            throw new \InvalidArgumentException("Field '{{ENTITY_NAME_ONE}}ID' must be an integer.");
        }

        ${{ENTITY_NAME_LOWER}} = $this->{{ENTITY_NAME_LOWER}}Repository->find{{ENTITY_NAME_ONE}}ById(${{ENTITY_NAME_LOWER}}ID);
        if (!${{ENTITY_NAME_LOWER}}) {
            $this->logger->warning("{{ENTITY_NAME_ONE}} with ID ${{ENTITY_NAME_LOWER}}ID not found.");
            throw new \InvalidArgumentException("{{ENTITY_NAME_ONE}} with ID ${{ENTITY_NAME_LOWER}}ID not found.");
        }
        return ${{ENTITY_NAME_LOWER}};
    }

    /**
     * Получение и проверка уникальности перевода.
     */
    public function ensureUniqueTranslation({{ENTITY_NAME}} ${{ENTITY_NAME_LOWER}}, int $languageId): void
    {
        $this->languagesProxyService->validateLanguageID($languageId);
        $existingTranslation = $this->translationRepository->findTranslationBy{{ENTITY_NAME_ONE}}AndLanguage(${{ENTITY_NAME_LOWER}}, $languageId);

        if ($existingTranslation) {
            $this->logger->error("Translation for {{ENTITY_NAME_ONE}} ID {${{ENTITY_NAME_LOWER}}->get{{ENTITY_NAME_ONE}}ID()} with Language ID {$languageId} already exists.");
            throw new \InvalidArgumentException("Translation for this language already exists for this {{ENTITY_NAME_ONE}}.");
        }
    }

    /**
     * Форматирование данных категории для ответа.
     *
     * @param {{ENTITY_NAME}} ${{ENTITY_NAME_LOWER}}
     * @return array
     */
    public function format{{ENTITY_NAME_ONE}}Data({{ENTITY_NAME}} ${{ENTITY_NAME_LOWER}}, bool $detail = false, ?int $languageId = null): array
    {
        ${{ENTITY_NAME_LOWER}}Data = [
            '{{ENTITY_NAME_ONE}}ID' => ${{ENTITY_NAME_LOWER}}->get{{ENTITY_NAME_ONE}}ID(),
            '{{ENTITY_NAME_ONE}}{{ENTITY_CODE_LINK}}' => ${{ENTITY_NAME_LOWER}}->get{{ENTITY_NAME_ONE}}{{ENTITY_CODE_LINK}}(),
        ];

        // Если требуется детальная информация и указан язык, получаем перевод
        if ($detail && $languageId) {
            try {
                $this->languagesProxyService->getLanguageById($languageId);
                $translation = $this->get{{ENTITY_NAME_ONE}}Translations(${{ENTITY_NAME_LOWER}}, $languageId);

                ${{ENTITY_NAME_LOWER}}Data['Translation'] = $translation
                    ? $this->format{{ENTITY_NAME_ONE}}TranslationsData($translation)
                    : 'Translation not available for the selected language.';

            } catch (\Exception $e) {
                $this->logger->warning("Failed to fetch translation for {{ENTITY_NAME_ONE}} ID {${{ENTITY_NAME_LOWER}}->get{{ENTITY_NAME_ONE}}ID()}: " . $e->getMessage());
                ${{ENTITY_NAME_LOWER}}Data['Translation'] = 'Language details unavailable.';
            }
        }
        return $detail ? ['{{ENTITY_NAME}}' => ${{ENTITY_NAME_LOWER}}Data] : ${{ENTITY_NAME_LOWER}}Data;
    }

    /**
     * Форматирование данных перевода категории для ответа.
     *
     * @param {{ENTITY_NAME_ONE}}Translations $translation
     * @return array
     */
    public function format{{ENTITY_NAME_ONE}}TranslationsData({{ENTITY_NAME_ONE}}Translations $translation): array
    {
        return [
            '{{ENTITY_NAME_ONE}}TranslationID' => $translation->get{{ENTITY_NAME_ONE}}TranslationID(),
            'LanguageID' => $translation->getLanguageID(),
            '{{ENTITY_NAME_ONE}}Name' => $translation->get{{ENTITY_NAME_ONE}}Name(),
            '{{ENTITY_NAME_ONE}}Description' => $translation->get{{ENTITY_NAME_ONE}}Description(),
        ];
    }

    public function get{{ENTITY_NAME_ONE}}Translations({{ENTITY_NAME}} ${{ENTITY_NAME_LOWER}}, int $languageId): ?{{ENTITY_NAME_ONE}}Translations
    {
        $translation = $this->translationRepository->findTranslationBy{{ENTITY_NAME_ONE}}AndLanguage(${{ENTITY_NAME_LOWER}}, $languageId);

        if (!$translation) {
            $this->logger->info("No translation found for {{ENTITY_NAME_ONE}} ID {${{ENTITY_NAME_LOWER}}->get{{ENTITY_NAME_ONE}}ID()} and language ID {$languageId}.");
        } else {
            $this->logger->info("Translation found: " . json_encode([
                    '{{ENTITY_NAME_ONE}}ID' => ${{ENTITY_NAME_LOWER}}->get{{ENTITY_NAME_ONE}}ID(),
                    'LanguageID' => $languageId,
                    'TranslationID' => $translation->get{{ENTITY_NAME_ONE}}TranslationID(),
                ]));
        }

        return $translation;
    }
}
