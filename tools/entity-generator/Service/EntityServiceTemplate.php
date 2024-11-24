<?php

namespace Module\{{ENTITY_DIR}}\{{ENTITY_NAME}}\Service;


use Module\{{ENTITY_DIR}}\{{ENTITY_NAME}}\Entity\{{ENTITY_NAME}};
use Module\{{ENTITY_DIR}}\{{ENTITY_NAME}}\Entity\{{ENTITY_NAME_ONE}}Translations;
use Module\{{ENTITY_DIR}}\{{ENTITY_NAME}}\Repository\{{ENTITY_NAME}}Repository;
use Module\{{ENTITY_DIR}}\{{ENTITY_NAME}}\Repository\{{ENTITY_NAME_ONE}}TranslationsRepository;
use Module\Common\Helpers\FieldUpdateHelper;
use Module\Common\Service\ImageService;
use Module\Common\Proxy\Core\LanguagesProxyService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class {{ENTITY_NAME}}Service
{
    private {{ENTITY_NAME}}Repository ${{ENTITY_NAME_LOWER}}Repository;
    private {{ENTITY_NAME_ONE}}TranslationsRepository $translationRepository;
    private LanguagesProxyService $languagesProxyService;
    private {{ENTITY_NAME}}ValidationService ${{ENTITY_NAME_LOWER}}ValidationService;
    private LoggerInterface $logger;
    private ImageService $imageService;
    private FieldUpdateHelper $helper;

    public function __construct(
        {{ENTITY_NAME}}Repository ${{ENTITY_NAME_LOWER}}Repository,
        {{ENTITY_NAME_ONE}}TranslationsRepository $translationRepository,
        LanguagesProxyService $languagesProxyService,
        {{ENTITY_NAME}}ValidationService ${{ENTITY_NAME_LOWER}}ValidationService,
        ImageService $imageService,
        FieldUpdateHelper $helper,
        LoggerInterface $logger
    ) {
        $this->{{ENTITY_NAME_LOWER}}Repository = ${{ENTITY_NAME_LOWER}}Repository;
        $this->translationRepository = $translationRepository;
        $this->languagesProxyService = $languagesProxyService;
        $this->{{ENTITY_NAME_LOWER}}ValidationService = ${{ENTITY_NAME_LOWER}}ValidationService;
        $this->imageService = $imageService;
        $this->helper = $helper;
        $this->logger = $logger;
    }


    /**
     * Получение всех категорий.
     * @return array
     */
    public function getAll{{ENTITY_NAME_ONE}}(): array
    {
        try {
            $this->logger->info("Executing getAll{{ENTITY_NAME}} method.");
            ${{ENTITY_NAME_LOWER}} = $this->{{ENTITY_NAME_LOWER}}Repository->findAll{{ENTITY_NAME}}();

            // Проверка, есть ли языки
            if (empty(${{ENTITY_NAME_LOWER}})) {
                $this->logger->info("No {{ENTITY_NAME_LOWER}} found in the database.");
                return [
                    '{{ENTITY_NAME_LOWER}}' => [],
                    'message' => 'No {{ENTITY_NAME_LOWER}} found in the database.'
                ];
            }
            // Форматируем каждую категорию и добавляем ключ для структурированного ответа
            return [
                '{{ENTITY_NAME_LOWER}}' => array_map([$this->{{ENTITY_NAME_LOWER}}ValidationService, 'format{{ENTITY_NAME_ONE}}Data'], ${{ENTITY_NAME_LOWER}}),
                'message' => '{{ENTITY_NAME_LOWER}} retrieved successfully.'
            ];
        } catch (\InvalidArgumentException $e) {
            $this->logger->error("Validation error while fetching {{ENTITY_NAME}}: " . $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error("Unable to fetch languages: " . $e->getMessage());
            throw new \RuntimeException("Unable to fetch {{ENTITY_NAME}} at the moment.", 0, $e);
        }
    }

    /**
     * Получение категории по ID вместе с переводами.
     *
     * @param int $id
     * @return array|null
     */
    public function get{{ENTITY_NAME_ONE}}ById(int $id): ?array
    {
        $this->logger->info("Executing get{{ENTITY_NAME_ONE}}ById method for ID: $id");
        // Используем validate{{ENTITY_NAME_ONE}}Exists для получения категории или выброса исключения
        ${{ENTITY_NAME_LOWER}} = $this->{{ENTITY_NAME_LOWER}}ValidationService->validate{{ENTITY_NAME_ONE}}Exists($id);
        $translations = $this->translationRepository->findTranslationsBy{{ENTITY_NAME_ONE}}(${{ENTITY_NAME_LOWER}});
        // Форматируем данные категории и переводов
        return [
            '{{ENTITY_NAME_LOWER}}' => $this->{{ENTITY_NAME_LOWER}}ValidationService->format{{ENTITY_NAME_ONE}}Data(${{ENTITY_NAME_LOWER}}),
            'translations' => array_map([$this->{{ENTITY_NAME_LOWER}}ValidationService, 'format{{ENTITY_NAME_ONE}}TranslationsData'], $translations),
            'message' => "{{ENTITY_NAME_ONE}} with ID $id retrieved successfully."
        ];
    }

    /**
     * Создание новой категории.
     *
     * @param array $data
     * @return array
     */
    public function create{{ENTITY_NAME_ONE}}(array $data): array
    {
        $this->logger->info("Executing create{{ENTITY_NAME_ONE}} method.");
        try {
            // Валидация данных для категории
            $this->{{ENTITY_NAME_LOWER}}ValidationService->validate{{ENTITY_NAME_ONE}}{{ENTITY_CODE_LINK}}($data);
            $this->{{ENTITY_NAME_LOWER}}ValidationService->ensureUnique{{ENTITY_NAME_ONE}}{{ENTITY_CODE_LINK}}($data['{{ENTITY_NAME_ONE}}{{ENTITY_CODE_LINK}}'] ?? null);

            // Создаем новую категорию
            ${{ENTITY_NAME_LOWER}} = new {{ENTITY_NAME}}();
            $this->helper->validateAndFilterFields(${{ENTITY_NAME_LOWER}}, $data);//проверяем список разрешенных полей
            ${{ENTITY_NAME_LOWER}}->set{{ENTITY_NAME_ONE}}{{ENTITY_CODE_LINK}}($data['{{ENTITY_NAME_ONE}}{{ENTITY_CODE_LINK}}']);

            // Сохраняем категорию в репозитории
            $this->{{ENTITY_NAME_LOWER}}Repository->save{{ENTITY_NAME_ONE}}(${{ENTITY_NAME_LOWER}}, true);
            $this->logger->info("{{ENTITY_NAME_ONE}} '{${{ENTITY_NAME_LOWER}}->get{{ENTITY_NAME_ONE}}{{ENTITY_CODE_LINK}}()}' created successfully.");

            // Форматируем ответ
            return [
                '{{ENTITY_NAME_ONE}}' => $this->{{ENTITY_NAME_LOWER}}ValidationService->format{{ENTITY_NAME_ONE}}Data(${{ENTITY_NAME_LOWER}}),
                'message' => '{{ENTITY_NAME_ONE}} added successfully.'
            ];
        } catch (\InvalidArgumentException $e) {
            // Логируем ошибку валидации, чтобы избежать дублирования в контроллере
            $this->logger->error("Validation failed for creating {{ENTITY_NAME_ONE}}: " . $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            // Логируем общую ошибку и выбрасываем исключение
            $this->logger->error("An unexpected error occurred while creating {{ENTITY_NAME_ONE}}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Добавление перевода для существующей категории.
     *
     * @param int ${{ENTITY_NAME_LOWER}}Id
     * @param array $data
     * @return array
     * @throws \InvalidArgumentException Если не удалось найти категорию или язык
     * @throws \Exception Если произошла непредвиденная ошибка
     */
    public function create{{ENTITY_NAME_ONE}}Translation(int ${{ENTITY_NAME_LOWER}}Id, array $data): array
    {
        $this->logger->info("Executing create{{ENTITY_NAME_ONE}}Translation method for {{ENTITY_NAME_ONE}} ID: ${{ENTITY_NAME_LOWER}}Id.");
        try {
            // Проверяем существование категории
            ${{ENTITY_NAME_LOWER}} = $this->{{ENTITY_NAME_LOWER}}ValidationService->validate{{ENTITY_NAME_ONE}}Exists(${{ENTITY_NAME_LOWER}}Id);

            // Проверяем наличие выполняем валидацию
            $this->{{ENTITY_NAME_LOWER}}ValidationService->validate{{ENTITY_NAME_ONE}}TranslationData($data);
            // Проверяем обязательность поля {{ENTITY_NAME_ONE}}Name
            $this->{{ENTITY_NAME_LOWER}}ValidationService->ensureUnique{{ENTITY_NAME_ONE}}Name($data['{{ENTITY_NAME_ONE}}Name'] ?? null);
            // Проверка на наличие LanguageID и указать, что это обязательный параметр
            $languageData = $this->languagesProxyService->validateLanguageID($data['LanguageID']  ?? null);

            // Проверка уникальности перевода
            $this->{{ENTITY_NAME_LOWER}}ValidationService->ensureUniqueTranslation(${{ENTITY_NAME_LOWER}}, $languageData['LanguageID']);

            // Создание нового перевода
            $translation = new {{ENTITY_NAME_ONE}}Translations();
            $this->helper->validateAndFilterFields($translation, $data);//проверяем список разрешенных полей
            $translation->set{{ENTITY_NAME_ONE}}ID(${{ENTITY_NAME_LOWER}});
            $translation->setLanguageID($languageData['LanguageID']);

            // Применение дополнительных полей
            foreach ($data as $field => $value) {
                $setter = 'set' . ucfirst($field);
                if (method_exists($translation, $setter) && !in_array($field, ['{{ENTITY_NAME_ONE}}ID', 'LanguageID'])) {
                    $translation->$setter($value);
                } elseif (!in_array($field, ['{{ENTITY_NAME_ONE}}ID', 'LanguageID'])) {
                    throw new \InvalidArgumentException("Field '$field' does not exist on {{ENTITY_NAME_ONE}}Translation entity.");
                }
            }
            $this->helper->validateAndFilterFields($translation, $data);//проверяем список разрешенных полей
            // Сохранение перевода
            $this->translationRepository->save{{ENTITY_NAME_ONE}}Translations($translation, true);
            $this->logger->info("Translation for {{ENTITY_NAME_ONE}} ID ${{ENTITY_NAME_LOWER}}Id created successfully.");

            return [
                '{{ENTITY_NAME_LOWER}}' => $this->{{ENTITY_NAME_LOWER}}ValidationService->format{{ENTITY_NAME_ONE}}Data(${{ENTITY_NAME_LOWER}}),
                'translation' => $this->{{ENTITY_NAME_LOWER}}ValidationService->format{{ENTITY_NAME_ONE}}TranslationsData($translation),
                'message' => '{{ENTITY_NAME_ONE}} translation added successfully.'
            ];

        } catch (\InvalidArgumentException $e) {
            // Логируем и передаем исключение с детализированным сообщением
            $this->logger->error("Validation failed for {{ENTITY_NAME_ONE}} ID ${{ENTITY_NAME_LOWER}}Id: " . $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            // Обработка неожиданных ошибок
            $this->logger->error("An unexpected error occurred while creating translation for {{ENTITY_NAME_ONE}} ID ${{ENTITY_NAME_LOWER}}Id: " . $e->getMessage());
            throw new \RuntimeException("Unable to add {{ENTITY_NAME_ONE}} translation", 0, $e);
        }
    }

    public function update{{ENTITY_NAME_ONE}}{{ENTITY_CODE_LINK}}(int ${{ENTITY_NAME_LOWER}}Id, array $data): array
    {
        $this->logger->info("Updating {{ENTITY_NAME_ONE}} {{ENTITY_CODE_LINK}} for {{ENTITY_NAME_ONE}} ID: ${{ENTITY_NAME_LOWER}}Id");

        try {
            // Получаем категорию по ID и проверяем ее существование
            ${{ENTITY_NAME_LOWER}} = $this->{{ENTITY_NAME_LOWER}}ValidationService->validate{{ENTITY_NAME_ONE}}Exists(${{ENTITY_NAME_LOWER}}Id);
            if (!${{ENTITY_NAME_LOWER}}) {
                $this->logger->warning("{{ENTITY_NAME_ONE}} with ID ${{ENTITY_NAME_LOWER}}Id not found for updating.");
                throw new \InvalidArgumentException("{{ENTITY_NAME_ONE}} with ID ${{ENTITY_NAME_LOWER}}Id not found.");
            }

            // Проверка наличия обязательных полей и их значений (в данных или в объекте)
            $requiredFields = ['{{ENTITY_NAME_ONE}}{{ENTITY_CODE_LINK}}'];
            foreach ($requiredFields as $field) {
                $value = $data[$field] ?? ${{ENTITY_NAME_LOWER}}->{'get' . $field}();
                if (empty($value)) {
                    throw new \InvalidArgumentException("Field '$field' is required and cannot be empty.");
                }
            }

            // Используем FieldUpdateHelper для обновления поля {{ENTITY_NAME_ONE}}{{ENTITY_CODE_LINK}} с проверками
            FieldUpdateHelper::updateFieldIfPresent(
                ${{ENTITY_NAME_LOWER}},
                $data,
                '{{ENTITY_NAME_ONE}}{{ENTITY_CODE_LINK}}',
                function ($new{{ENTITY_CODE_LINK}}) use (${{ENTITY_NAME_LOWER}}Id) {
                    $this->{{ENTITY_NAME_LOWER}}ValidationService->ensureUnique{{ENTITY_NAME_ONE}}{{ENTITY_CODE_LINK}}($new{{ENTITY_CODE_LINK}}, ${{ENTITY_NAME_LOWER}}Id);
                    $this->{{ENTITY_NAME_LOWER}}ValidationService->validate{{ENTITY_NAME_ONE}}{{ENTITY_CODE_LINK}}(['{{ENTITY_NAME_ONE}}{{ENTITY_CODE_LINK}}' => $new{{ENTITY_CODE_LINK}}]);
                }
            );

            $this->helper->validateAndFilterFields(${{ENTITY_NAME_LOWER}}, $data);//проверяем список разрешенных полей
            $this->{{ENTITY_NAME_LOWER}}Repository->save{{ENTITY_NAME_ONE}}(${{ENTITY_NAME_LOWER}}, true);

            $this->logger->info("{{ENTITY_NAME_ONE}} {{ENTITY_CODE_LINK}} updated successfully for {{ENTITY_NAME_ONE}} ID: ${{ENTITY_NAME_LOWER}}Id");

            return [
                '{{ENTITY_NAME_ONE}}' => $this->{{ENTITY_NAME_LOWER}}ValidationService->format{{ENTITY_NAME_ONE}}Data(${{ENTITY_NAME_LOWER}}),
                'message' => '{{ENTITY_NAME_ONE}} {{ENTITY_CODE_LINK}} updated successfully.'
            ];
        } catch (\InvalidArgumentException $e) {
            $this->logger->error("Validation failed for {{ENTITY_NAME_ONE}} ID ${{ENTITY_NAME_LOWER}}Id: " . $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error("An unexpected error occurred while updating {{ENTITY_NAME_ONE}} {{ENTITY_CODE_LINK}} for ID ${{ENTITY_NAME_LOWER}}Id: " . $e->getMessage());
            throw new \RuntimeException("Unable to update {{ENTITY_NAME_ONE}} {{ENTITY_CODE_LINK}}", 0, $e);
        }
    }

    //Обновление OgImage картинки у категории
//    public function update{{ENTITY_NAME_ONE}}Image(int ${{ENTITY_NAME_LOWER}}Id, ?UploadedFile $file): array
//    {
//        $this->logger->info("Executing update{{ENTITY_NAME_ONE}}Image method for {{ENTITY_NAME_ONE}} ID: ${{ENTITY_NAME_LOWER}}Id.");
//
//        try {
//            // Проверяем, существует ли категория
//            ${{ENTITY_NAME_LOWER}} = $this->{{ENTITY_NAME_LOWER}}ValidationService->validate{{ENTITY_NAME_ONE}}Exists(${{ENTITY_NAME_LOWER}}Id);
//            $oldImagePath = ${{ENTITY_NAME_LOWER}}->getOgImage();
//            // Загружаем новое изображение и получаем путь
//            $newImagePath = $this->imageService->uploadOgImage($file, ${{ENTITY_NAME_LOWER}}Id, '{{ENTITY_NAME_LOWER_ONLY}}', $oldImagePath);
//            // Устанавливаем новый путь для изображения
//            ${{ENTITY_NAME_LOWER}}->setOgImage($newImagePath);
//
//            // Сохраняем изменения
//            $this->{{ENTITY_NAME_LOWER}}Repository->save{{ENTITY_NAME_ONE}}(${{ENTITY_NAME_LOWER}}, true);
//            $this->logger->info("Image for {{ENTITY_NAME_ONE}} ID ${{ENTITY_NAME_LOWER}}Id updated successfully.");
//
//            // Возвращаем успешный ответ с новыми данными
//            return [
//                '{{ENTITY_NAME_ONE}}' => $this->{{ENTITY_NAME_LOWER}}ValidationService->format{{ENTITY_NAME_ONE}}Data(${{ENTITY_NAME_LOWER}}),
//                'message' => '{{ENTITY_NAME_ONE}} image updated successfully.'
//            ];
//        } catch (\InvalidArgumentException $e) {
//            // Логируем ошибки валидации и выбрасываем исключение
//            $this->logger->error("Validation failed for updating {{ENTITY_NAME_ONE}} image: " . $e->getMessage());
//            throw $e;
//        } catch (\Exception $e) {
//            // Логируем общую ошибку
//            $this->logger->error("An unexpected error occurred while updating {{ENTITY_NAME_ONE}} image: " . $e->getMessage());
//            throw new \RuntimeException("Unable to update {{ENTITY_NAME_ONE}} image at this time.", 0, $e);
//        }
//    }


    public function update{{ENTITY_NAME_ONE}}Translation(int ${{ENTITY_NAME_LOWER}}Id, int $translationId, array $data): array
    {
        $this->logger->info("Updating translation for {{ENTITY_NAME_ONE}} ID: ${{ENTITY_NAME_LOWER}}Id and Translation ID: $translationId");

        try {
            ${{ENTITY_NAME_LOWER}} = $this->{{ENTITY_NAME_LOWER}}ValidationService->validate{{ENTITY_NAME_ONE}}Exists(${{ENTITY_NAME_LOWER}}Id);

            // Поиск перевода по ID
            $translation = $this->translationRepository->find($translationId);
            if (!$translation || $translation->get{{ENTITY_NAME_ONE}}ID()->get{{ENTITY_NAME_ONE}}ID() !== ${{ENTITY_NAME_LOWER}}Id) {
                $this->logger->error("Translation for {{ENTITY_NAME_ONE}} ID ${{ENTITY_NAME_LOWER}}Id and Translation ID $translationId not found.");
                throw new \InvalidArgumentException("Translation with this ID does not exist for this {{ENTITY_NAME_ONE}}.");
            }

            // Проверка, что LanguageID не был передан в запросе
            $this->languagesProxyService->checkImmutableLanguageID($data, $translation->getLanguageID());

            // Проверка наличия обязательных полей и их значений (в данных или в объекте)
            $requiredFields = ['{{ENTITY_NAME_ONE}}Name'];
            foreach ($requiredFields as $field) {
                $value = $data[$field] ?? $translation->{'get' . $field}();
                if (empty($value)) {
                    throw new \InvalidArgumentException("Field '$field' is required and cannot be empty.");
                }
            }
            // Валидация всех данных, переданных в $data
            $this->{{ENTITY_NAME_LOWER}}ValidationService->validate{{ENTITY_NAME_ONE}}TranslationData($data);

            // Обновление поля {{ENTITY_NAME_ONE}}Name с проверкой уникальности и наличия
            FieldUpdateHelper::updateFieldIfPresent(
                $translation,
                $data,
                '{{ENTITY_NAME_ONE}}Name',
                function ($newName) use ($translationId) {
                    $this->{{ENTITY_NAME_LOWER}}ValidationService->ensureUnique{{ENTITY_NAME_ONE}}Name($newName, $translationId);
                }
            );

            // Обновление полей перевода, игнорируя LanguageID
            foreach ($data as $field => $value) {
                $setter = 'set' . ucfirst($field);
                if (method_exists($translation, $setter) && !in_array($field, ['{{ENTITY_NAME_ONE}}ID'])) {
                    $translation->$setter($value);
                } elseif (!in_array($field, ['{{ENTITY_NAME_ONE}}ID'])) {
                    throw new \InvalidArgumentException("Field '$field' does not exist on {{ENTITY_NAME_ONE}}Translation entity.");
                }
            }
            $this->helper->validateAndFilterFields($translation, $data);//проверяем список разрешенных полей
            $this->translationRepository->save{{ENTITY_NAME_ONE}}Translations($translation, true);
            $this->logger->info("Translation updated successfully for {{ENTITY_NAME_ONE}} ID: ${{ENTITY_NAME_LOWER}}Id and Translation ID: $translationId");

            return [
                '{{ENTITY_NAME_ONE}}' => $this->{{ENTITY_NAME_LOWER}}ValidationService->format{{ENTITY_NAME_ONE}}Data(${{ENTITY_NAME_LOWER}}),
                'translation' => $this->{{ENTITY_NAME_LOWER}}ValidationService->format{{ENTITY_NAME_ONE}}TranslationsData($translation),
                'message' => '{{ENTITY_NAME_ONE}} translation updated successfully.'
            ];
        } catch (\InvalidArgumentException $e) {
            $this->logger->error("Validation failed for {{ENTITY_NAME_ONE}} ID ${{ENTITY_NAME_LOWER}}Id and Translation ID $translationId: " . $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error("An unexpected error occurred while updating translation for {{ENTITY_NAME_ONE}} ID ${{ENTITY_NAME_LOWER}}Id and Translation ID $translationId: " . $e->getMessage());
            throw new \RuntimeException("Unable to update {{ENTITY_NAME_ONE}} translation", 0, $e);
        }
    }

    /**
     * Удаление перевода категории.
     *
     * @param int ${{ENTITY_NAME_LOWER}}Id
     * @param int $translationId
     * @return array
     */
    public function delete{{ENTITY_NAME_ONE}}Translation(int ${{ENTITY_NAME_LOWER}}Id, int $translationId): array
    {
        $this->logger->info("Deleting translation with ID: $translationId for {{ENTITY_NAME_ONE}} ID: ${{ENTITY_NAME_LOWER}}Id");

        try {
            // Проверка существования категории
            $this->{{ENTITY_NAME_LOWER}}ValidationService->validate{{ENTITY_NAME_ONE}}Exists(${{ENTITY_NAME_LOWER}}Id);

            // Проверка существования перевода для данной категории
            $translation = $this->translationRepository->find($translationId);
            if (!$translation || $translation->get{{ENTITY_NAME_ONE}}ID()->get{{ENTITY_NAME_ONE}}ID() !== ${{ENTITY_NAME_LOWER}}Id) {
                $this->logger->error("Translation with ID $translationId does not exist for {{ENTITY_NAME_ONE}} ID ${{ENTITY_NAME_LOWER}}Id.");
                throw new \InvalidArgumentException("Translation with this ID does not exist for this {{ENTITY_NAME_ONE}}.");
            }

            // Удаление перевода
            $this->translationRepository->delete{{ENTITY_NAME_ONE}}Translations($translation, true);
            $this->logger->info("Translation with ID $translationId successfully deleted for {{ENTITY_NAME_ONE}} ID ${{ENTITY_NAME_LOWER}}Id.");

            return [
                'message' => "Translation with ID $translationId successfully deleted for {{ENTITY_NAME_ONE}} ID ${{ENTITY_NAME_LOWER}}Id.",
            ];
        } catch (\InvalidArgumentException $e) {
            $this->logger->warning($e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error("An unexpected error occurred while deleting translation with ID $translationId for {{ENTITY_NAME_ONE}} ID ${{ENTITY_NAME_LOWER}}Id: " . $e->getMessage());
            throw new \RuntimeException("Unable to delete {{ENTITY_NAME_ONE}} translation", 0, $e);
        }
    }


    /**
     * Удаление категории.
     *
     * @param int ${{ENTITY_NAME_LOWER}}Id
     * @return array
     */
    public function delete{{ENTITY_NAME_ONE}}(int ${{ENTITY_NAME_LOWER}}Id): array
    {
        try {
            $this->logger->info("Executing delete{{ENTITY_NAME_ONE}} method for ID: ${{ENTITY_NAME_LOWER}}Id");

            ${{ENTITY_NAME_LOWER}} = $this->{{ENTITY_NAME_LOWER}}ValidationService->validate{{ENTITY_NAME_ONE}}Exists(${{ENTITY_NAME_LOWER}}Id);


            // Удаляем переводы категории
            $translations = $this->translationRepository->findTranslationsBy{{ENTITY_NAME_ONE}}(${{ENTITY_NAME_LOWER}});
            foreach ($translations as $translation) {
                $this->translationRepository->delete{{ENTITY_NAME_ONE}}Translations($translation, true);
            }

            // Удаляем саму категорию
            $this->{{ENTITY_NAME_LOWER}}Repository->delete{{ENTITY_NAME_ONE}}(${{ENTITY_NAME_LOWER}}, true);
            $this->logger->info("{{ENTITY_NAME_ONE}} with ID ${{ENTITY_NAME_LOWER}}Id and its translations successfully deleted.");

            return [
                'message' => "{{ENTITY_NAME_ONE}} with ID ${{ENTITY_NAME_LOWER}}Id and its translations successfully deleted.",
            ];
        } catch (\InvalidArgumentException $e) {
            $this->logger->warning($e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error("An unexpected error occurred while deleting {{ENTITY_NAME_ONE}} with ID ${{ENTITY_NAME_LOWER}}Id: " . $e->getMessage());
            throw $e;
        }
    }


    /*/
    Методы для демо данных
    /*/
    public function seed{{ENTITY_NAME_ONE}}AndTranslations(): array
    {
        $this->logger->info("Executing seedJobTitlesAndTranslations method.");

        // Данные для предустановленных типов индустрий
        ${{ENTITY_NAME_LOWER}}sData = [
            ["{{ENTITY_NAME_ONE}}{{ENTITY_CODE_LINK}}" => "technology"],
            ["{{ENTITY_NAME_ONE}}{{ENTITY_CODE_LINK}}" => "finance"],
            ["{{ENTITY_NAME_ONE}}{{ENTITY_CODE_LINK}}" => "healthcare"]
        ];


        $created{{ENTITY_NAME_ONE}} = [];
        ${{ENTITY_NAME_LOWER}}Ids = [];

        // Создаём должности и сохраняем их ID
        foreach (${{ENTITY_NAME_LOWER}}sData as ${{ENTITY_NAME_LOWER}}Data) {
            try {
                $this->{{ENTITY_NAME_LOWER}}ValidationService->validate{{ENTITY_NAME_ONE}}{{ENTITY_CODE_LINK}}(${{ENTITY_NAME_LOWER}}Data);
                $this->{{ENTITY_NAME_LOWER}}ValidationService->ensureUnique{{ENTITY_NAME_ONE}}{{ENTITY_CODE_LINK}}(${{ENTITY_NAME_LOWER}}Data['{{ENTITY_NAME_ONE}}{{ENTITY_CODE_LINK}}']);

                ${{ENTITY_NAME_LOWER}} = new {{ENTITY_NAME}}();
                ${{ENTITY_NAME_LOWER}}->set{{ENTITY_NAME_ONE}}{{ENTITY_CODE_LINK}}(${{ENTITY_NAME_LOWER}}Data['{{ENTITY_NAME_ONE}}{{ENTITY_CODE_LINK}}']);
                $this->{{ENTITY_NAME_LOWER}}Repository->save{{ENTITY_NAME_ONE}}(${{ENTITY_NAME_LOWER}}, true);

                $created{{ENTITY_NAME_ONE}}[] = $this->{{ENTITY_NAME_LOWER}}ValidationService->format{{ENTITY_NAME_ONE}}Data(${{ENTITY_NAME_LOWER}});
                ${{ENTITY_NAME_LOWER}}Ids[${{ENTITY_NAME_LOWER}}Data['{{ENTITY_NAME_ONE}}{{ENTITY_CODE_LINK}}']] = ${{ENTITY_NAME_LOWER}}->get{{ENTITY_NAME_ONE}}ID(); // Сохраняем ID должности

                $this->logger->info("{{ENTITY_NAME_ONE}} {{ENTITY_CODE_LINK}} '{${{ENTITY_NAME_LOWER}}Data['{{ENTITY_NAME_ONE}}{{ENTITY_CODE_LINK}}']}' created successfully.");
            } catch (\InvalidArgumentException $e) {
                $this->logger->warning("{{ENTITY_NAME_ONE}} {{ENTITY_CODE_LINK}} '{${{ENTITY_NAME_LOWER}}Data['{{ENTITY_NAME_ONE}}{{ENTITY_CODE_LINK}}']}' already exists or is invalid. Skipping.");
            }
        }

        // Данные для переводов Индустрии, привязанные к {{ENTITY_NAME_ONE}}ID
        $translationsData = [
            ${{ENTITY_NAME_LOWER}}Ids['technology'] ?? null => [
                ["{{ENTITY_NAME_ONE}}Name" => "Технологии", "{{ENTITY_NAME_ONE}}Description" => "Все отрасли, связанные с информационными технологиями", "LanguageID" => 2],
                ["{{ENTITY_NAME_ONE}}Name" => "Technology", "{{ENTITY_NAME_ONE}}Description" => "All technology-related {{ENTITY_NAME}}", "LanguageID" => 1]
            ],
            ${{ENTITY_NAME_LOWER}}Ids['finance'] ?? null => [
                ["{{ENTITY_NAME_ONE}}Name" => "Финансы", "{{ENTITY_NAME_ONE}}Description" => "Финансовые услуги и рынки", "LanguageID" => 2],
                ["{{ENTITY_NAME_ONE}}Name" => "Finance", "{{ENTITY_NAME_ONE}}Description" => "Financial services and markets", "LanguageID" => 1]
            ],
            ${{ENTITY_NAME_LOWER}}Ids['healthcare'] ?? null => [
                ["{{ENTITY_NAME_ONE}}Name" => "Здравоохранение", "{{ENTITY_NAME_ONE}}Description" => "Услуги и технологии в здравоохранении", "LanguageID" => 2],
                ["{{ENTITY_NAME_ONE}}Name" => "Healthcare", "{{ENTITY_NAME_ONE}}Description" => "Healthcare services and technologies", "LanguageID" => 1]
            ]
        ];

        $createdTranslations = [];

        // Создаём переводы для каждой должности, используя их ID
        foreach ($translationsData as ${{ENTITY_NAME_LOWER}}Ids => $translations) {
            if (!${{ENTITY_NAME_LOWER}}Ids) {
                continue; // Пропускаем, если ID не найден
            }

            ${{ENTITY_NAME_LOWER}} = $this->{{ENTITY_NAME_LOWER}}Repository->find{{ENTITY_NAME_ONE}}ById(${{ENTITY_NAME_LOWER}}Ids);

            foreach ($translations as $translationData) {
                try {
                    $languageData = $this->languagesProxyService->validateLanguageID($translationData['LanguageID']);
                    $languageId = $languageData['LanguageID'];
                    $this->{{ENTITY_NAME_LOWER}}ValidationService->ensureUniqueTranslation(${{ENTITY_NAME_LOWER}}, $languageId);

                    $translation = new {{ENTITY_NAME_ONE}}Translations();
                    $translation->set{{ENTITY_NAME_ONE}}ID(${{ENTITY_NAME_LOWER}});
                    $translation->setLanguageID($languageId);
                    $translation->set{{ENTITY_NAME_ONE}}Name($translationData['{{ENTITY_NAME_ONE}}Name']);
                    $translation->set{{ENTITY_NAME_ONE}}Description($translationData['{{ENTITY_NAME_ONE}}Description']);

                    $this->translationRepository->save{{ENTITY_NAME_ONE}}Translations($translation, true);
                    $createdTranslations[] = $this->{{ENTITY_NAME_LOWER}}ValidationService->format{{ENTITY_NAME_ONE}}TranslationsData($translation);

                    $this->logger->info("Translation for {{ENTITY_NAME_ONE}} ID '{${{ENTITY_NAME_LOWER}}Ids}' and LanguageID '{$languageId}' created successfully.");
                } catch (\Exception $e) {
                    $this->logger->error("Failed to add translation for {{ENTITY_NAME_ONE}} ID '${{ENTITY_NAME_LOWER}}Ids': " . $e->getMessage());
                }
            }
        }

        return [
            '{{ENTITY_NAME_LOWER}}' => $created{{ENTITY_NAME_ONE}},
            'translations' => $createdTranslations,
            'message' => '{{ENTITY_NAME_ONE}} and translations seeded successfully.'
        ];
    }
}
