<?php

namespace Module\Common\Proxy\{{ENTITY_DIR}};

use Module\{{ENTITY_DIR}}\{{ENTITY_NAME}}\Entity\{{ENTITY_NAME_ONE}}Translations;
use Module\{{ENTITY_DIR}}\{{ENTITY_NAME}}\Repository\{{ENTITY_NAME}}Repository;
use Module\{{ENTITY_DIR}}\{{ENTITY_NAME}}\Repository\{{ENTITY_NAME_ONE}}TranslationsRepository;
use Module\{{ENTITY_DIR}}\{{ENTITY_NAME}}\Service\{{ENTITY_NAME}}ValidationService;
use Psr\Log\LoggerInterface;

class {{ENTITY_NAME}}ProxyService
{
    private {{ENTITY_NAME}}Repository ${{ENTITY_NAME_LOWER}}Repository;
    private {{ENTITY_NAME_ONE}}TranslationsRepository $translationRepository;
    private {{ENTITY_NAME}}ValidationService ${{ENTITY_NAME_LOWER}}ValidationService;
    private LoggerInterface $logger;

    public function __construct(
        {{ENTITY_NAME}}Repository ${{ENTITY_NAME_LOWER}}Repository,
        {{ENTITY_NAME_ONE}}TranslationsRepository $translationRepository,
        {{ENTITY_NAME}}ValidationService ${{ENTITY_NAME_LOWER}}ValidationService,
        LoggerInterface $logger,

    ) {
        $this->{{ENTITY_NAME_LOWER}}Repository = ${{ENTITY_NAME_LOWER}}Repository;
        $this->translationRepository = $translationRepository;
        $this->{{ENTITY_NAME_LOWER}}ValidationService = ${{ENTITY_NAME_LOWER}}ValidationService;
        $this->logger = $logger;
    }

    /**
     * Получение всех языков.
     */
    public function getAll{{ENTITY_NAME}}(): array
    {
        try {
            $this->logger->info("Fetching all languages directly from repository.");
            $languages = $this->{{ENTITY_NAME_LOWER}}Repository->findAll{{ENTITY_NAME}}();

            if (empty($languages)) {
                $this->logger->info("No {{ENTITY_NAME_ONE}} found in the database.");
                return [];
            }

            return array_map(
                fn($language) => $this->{{ENTITY_NAME_LOWER}}ValidationService->format{{ENTITY_NAME_LOWER}}Data($language),
                $languages
            );
        } catch (\Exception $e) {
            $this->logger->error("Failed to fetch all {{ENTITY_NAME_LOWER}}: " . $e->getMessage());
            throw new \RuntimeException("Unable to fetch all {{ENTITY_NAME_LOWER}}.");
        }
    }

    /**
     * Получение языка по ID.
     */
    public function get{{ENTITY_NAME_ONE}}ById(int ${{ENTITY_NAME_LOWER}}Id, int $languageId): array
    {
        try {
            $this->logger->info("Fetching {{ENTITY_NAME_LOWER}} with ID {${{ENTITY_NAME_LOWER}}Id}.");
            ${{ENTITY_NAME_LOWER}} = $this->{{ENTITY_NAME_LOWER}}ValidationService->validate{{ENTITY_NAME_ONE}}Exists(${{ENTITY_NAME_LOWER}}Id);

            // Указываем, что не требуется детализированный формат
            return $this->{{ENTITY_NAME_LOWER}}ValidationService->format{{ENTITY_NAME_ONE}}Data(${{ENTITY_NAME_LOWER}}, true, $languageId);
        } catch (\InvalidArgumentException $e) {
            $this->logger->warning("Validation error for {{ENTITY_NAME_ONE}} ID {${{ENTITY_NAME_LOWER}}Id}: " . $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error("Failed to fetch {{ENTITY_NAME_ONE}} with ID {${{ENTITY_NAME_LOWER}}Id}: " . $e->getMessage());
            throw new \RuntimeException("Unable to fetch {{ENTITY_NAME_ONE}} with ID {${{ENTITY_NAME_LOWER}}Id}.");
        }
    }


    /**
     * Валидация существования категории по ID.
     */
    public function validate{{ENTITY_NAME_ONE}}Exists(mixed ${{ENTITY_NAME_LOWER}}Id): array
    {
        try {
            $this->logger->info("Validating {{ENTITY_NAME_ONE}} ID: {${{ENTITY_NAME_LOWER}}Id}");
            ${{ENTITY_NAME_LOWER}} = $this->{{ENTITY_NAME_LOWER}}ValidationService->validate{{ENTITY_NAME_ONE}}Exists(${{ENTITY_NAME_LOWER}}Id);

            // Получаем отформатированные данные
            $formatted{{ENTITY_NAME_ONE}} = $this->{{ENTITY_NAME_LOWER}}ValidationService->format{{ENTITY_NAME_ONE}}Data(${{ENTITY_NAME_LOWER}}, true);

            // Проверяем наличие ключа '{{ENTITY_NAME_LOWER}}ID'
            if (!isset($formatted{{ENTITY_NAME_ONE}}['{{ENTITY_NAME}}']['{{ENTITY_NAME_ONE}}ID'])) {
                throw new \InvalidArgumentException("{{ENTITY_NAME_ONE}} data does not contain '{{ENTITY_NAME_ONE}}ID'.");
            }
            $this->logger->info("Validated {{ENTITY_NAME_ONE}}: " . json_encode($formatted{{ENTITY_NAME_ONE}}));
            return $formatted{{ENTITY_NAME_ONE}}['{{ENTITY_NAME_ONE}}'];
        } catch (\InvalidArgumentException $e) {
            $this->logger->warning("Validation failed for {{ENTITY_NAME_ONE}} ID {${{ENTITY_NAME_LOWER}}Id}: {$e->getMessage()}");
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error("Unexpected error during validation for {{ENTITY_NAME_ONE}} ID {${{ENTITY_NAME_LOWER}}Id}: {$e->getMessage()}");
            throw new \RuntimeException("An unexpected error occurred during {{ENTITY_NAME_ONE}} validation.");
        }
    }


}
