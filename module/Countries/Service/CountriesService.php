<?php

namespace Module\Countries\Service;

use Module\Common\Helpers\FieldUpdateHelper;
use Module\Common\Service\ImageService;
use Module\Countries\Entity\Countries;
use Module\Countries\Entity\CountryTranslation;
use Module\Countries\Repository\CountriesRepository;
use Module\Countries\Repository\CountryTranslationRepository;
use \Module\Countries\Service\CountriesValidationService;
use Module\Languages\Repository\LanguagesRepository;
use Module\Languages\Service\LanguagesValidationService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class CountriesService
{
    private CountriesRepository $countryRepository;
    private CountryTranslationRepository $translationRepository;
    private LanguagesRepository $languageRepository;
    private LanguagesValidationService $languagesValidationService;
    private CountriesValidationService $countriesValidationService;
    private LoggerInterface $logger;
    private ImageService $imageService;
    private FieldUpdateHelper $helper;

    public function __construct(
        CountriesRepository $countryRepository,
        CountryTranslationRepository $translationRepository,
        LanguagesRepository $languageRepository,
        LanguagesValidationService $languagesValidationService,
        CountriesValidationService $countriesValidationService,
        ImageService $imageService,
        FieldUpdateHelper $helper,
        LoggerInterface $logger
    ) {
        $this->countryRepository = $countryRepository;
        $this->translationRepository = $translationRepository;
        $this->languageRepository = $languageRepository;
        $this->languagesValidationService = $languagesValidationService;
        $this->countriesValidationService = $countriesValidationService;
        $this->imageService = $imageService;
        $this->helper = $helper;
        $this->logger = $logger;
    }


    /**
     * Получение всех Country.
     * @return array
     */
    public function getAllCountries(): array
    {
        try {
            $this->logger->info("Executing getAllCountries method.");
            $countries = $this->countryRepository->findAllCountries();

            // Проверка, есть ли языки
            if (empty($countries)) {
                $this->logger->info("No Countries found in the database.");
                return [
                    'countries' => [],
                    'message' => 'No Countries found in the database.'
                ];
            }
            // Форматируем каждую страну и добавляем ключ для структурированного ответа
            return [
                'countries' => array_map([$this->countriesValidationService, 'formatCountryData'], $countries),
                'message' => 'Countries retrieved successfully.'
            ];
        } catch (\InvalidArgumentException $e) {
            $this->logger->error("Validation error while fetching countries: " . $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error("Unable to fetch languages: " . $e->getMessage());
            throw new \RuntimeException("Unable to fetch countries at the moment.", 0, $e);
        }
    }

    /**
     * Получение Country по ID вместе с переводами.
     *
     * @param int $id
     * @return array|null
     */
    public function getCountryById(int $id): ?array
    {
        $this->logger->info("Executing getCountryById method for ID: $id");
        // Используем validateCountryExists для получения стран или выброса исключения
        $country = $this->countriesValidationService->validateCountryExists($id);
        $translations = $this->translationRepository->findTranslationsByCountry($country);
        // Форматируем данные Стран и переводов
        return [
            'country' => $this->countriesValidationService->formatCountryData($country),
            'translations' => array_map([$this->countriesValidationService, 'formatCountryTranslationData'], $translations),
            'message' => "Country with ID $id retrieved successfully."
        ];
    }

    /**
     * Создание новой Страны.
     *
     * @param array $data
     * @return array
     */
    public function createCountry(array $data): array
    {
        $this->logger->info("Executing createCountry method.");
        try {
            // Валидация данных для Страны
            $this->countriesValidationService->validateCountryLink($data);
            $this->countriesValidationService->ensureUniqueCountryLink($data['CountryLink'] ?? null);

            // Создаем новую Страну
            $country = new Countries();
            $this->helper->validateAndFilterFields($country, $data);//проверяем список разрешенных полей
            $country->setCountryLink($data['CountryLink']);

            // Сохраняем Страну в репозитории
            $this->countryRepository->saveCountry($country, true);
            $this->logger->info("Country '{$country->getCountryLink()}' created successfully.");

            // Форматируем ответ
            return [
                'country' => $this->countriesValidationService->formatCountryData($country),
                'message' => 'Country added successfully.'
            ];
        } catch (\InvalidArgumentException $e) {
            // Логируем ошибку валидации, чтобы избежать дублирования в контроллере
            $this->logger->error("Validation failed for creating country: " . $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            // Логируем общую ошибку и выбрасываем исключение
            $this->logger->error("An unexpected error occurred while creating country: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Добавление перевода для существующей страны.
     *
     * @param int $countryId
     * @param array $data
     * @return array
     * @throws \InvalidArgumentException Если не удалось найти страну или язык
     * @throws \Exception Если произошла непредвиденная ошибка
     */
    public function createCountryTranslation(int $countryId, array $data): array
    {
        $this->logger->info("Executing createCountryTranslation method for Country ID: $countryId.");
        try {
            // Проверяем существование страны
            $country = $this->countriesValidationService->validateCountryExists($countryId);

            // Проверяем наличие выполняем валидацию
            $this->countriesValidationService->validateCountryTranslationData($data);
            // Проверяем обязательность поля CountryName
            $this->countriesValidationService->ensureUniqueCountryName($data['CountryName'] ?? null);
            // Проверка на наличие LanguageID и указать, что это обязательный параметр
            $language = $this->languagesValidationService->validateLanguageID($data['LanguageID']  ?? null);

            // Проверка уникальности перевода
            $this->countriesValidationService->ensureUniqueTranslation($country, $language);

            // Создание нового перевода
            $translation = new CountryTranslation();
            $this->helper->validateAndFilterFields($translation, $data);//проверяем список разрешенных полей
            $translation->setCountryID($country);
            $translation->setLanguageID($language);

            // Применение дополнительных полей
            foreach ($data as $field => $value) {
                $setter = 'set' . ucfirst($field);
                if (method_exists($translation, $setter) && !in_array($field, ['CountryID', 'LanguageID'])) {
                    $translation->$setter($value);
                } elseif (!in_array($field, ['CountryID', 'LanguageID'])) {
                    throw new \InvalidArgumentException("Field '$field' does not exist on CountryTranslation entity.");
                }
            }
            $this->helper->validateAndFilterFields($translation, $data);//проверяем список разрешенных полей
            // Сохранение перевода
            $this->translationRepository->saveCountryTranslation($translation, true);
            $this->logger->info("Translation for Country ID $countryId created successfully.");

            return [
                'country' => $this->countriesValidationService->formatCountryData($country),
                'translation' => $this->countriesValidationService->formatCountryTranslationData($translation),
                'message' => 'Country translation added successfully.'
            ];

        } catch (\InvalidArgumentException $e) {
            // Логируем и передаем исключение с детализированным сообщением
            $this->logger->error("Validation failed for Country ID $countryId: " . $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            // Обработка неожиданных ошибок
            $this->logger->error("An unexpected error occurred while creating translation for Country ID $countryId: " . $e->getMessage());
            throw new \RuntimeException("Unable to add country translation", 0, $e);
        }
    }

    public function updateCountryLink(int $countryId, array $data): array
    {
        $this->logger->info("Updating Country link for Country ID: $countryId");

        try {
            // Получаем страну по ID и проверяем ее существование
            $country = $this->countriesValidationService->validateCountryExists($countryId);
            if (!$country) {
                $this->logger->warning("Country with ID $countryId not found for updating.");
                throw new \InvalidArgumentException("Country with ID $countryId not found.");
            }

            // Проверка наличия обязательных полей и их значений (в данных или в объекте)
            $requiredFields = ['CountryLink'];
            foreach ($requiredFields as $field) {
                $value = $data[$field] ?? $country->{'get' . $field}();
                if (empty($value)) {
                    throw new \InvalidArgumentException("Field '$field' is required and cannot be empty.");
                }
            }

            // Используем FieldUpdateHelper для обновления поля CountryLink с проверками
            FieldUpdateHelper::updateFieldIfPresent(
                $country,
                $data,
                'CountryLink',
                function ($newLink) use ($countryId) {
                    $this->countriesValidationService->ensureUniqueCountryLink($newLink, $countryId);
                    $this->countriesValidationService->validateCountryLink(['CountryLink' => $newLink]);
                }
            );

            $this->helper->validateAndFilterFields($country, $data);//проверяем список разрешенных полей
            $this->countryRepository->saveCountry($country, true);

            $this->logger->info("Country link updated successfully for Country ID: $countryId");

            return [
                'country' => $this->countriesValidationService->formatCountryData($country),
                'message' => 'Country link updated successfully.'
            ];
        } catch (\InvalidArgumentException $e) {
            $this->logger->error("Validation failed for Country ID $countryId: " . $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error("An unexpected error occurred while updating country link for ID $countryId: " . $e->getMessage());
            throw new \RuntimeException("Unable to update country link", 0, $e);
        }
    }

    //Обновление OgImage картинки у Страны
    public function updateCountryImage(int $countryId, ?UploadedFile $file): array
    {
        $this->logger->info("Executing updateCountryImage method for Country ID: $countryId.");

        try {
            // Проверяем, существует ли страна
            $country = $this->countriesValidationService->validateCountryExists($countryId);
            $oldImagePath = $country->getOgImage();
            // Загружаем новое изображение и получаем путь
            $newImagePath = $this->imageService->uploadOgImage($file, $countryId, 'countries', $oldImagePath);
            // Устанавливаем новый путь для изображения
            $country->setOgImage($newImagePath);

            // Сохраняем изменения
            $this->countryRepository->saveCountry($country, true);
            $this->logger->info("Image for Country ID $countryId updated successfully.");

            // Возвращаем успешный ответ с новыми данными
            return [
                'country' => $this->countriesValidationService->formatCountryData($country),
                'message' => 'Country image updated successfully.'
            ];
        } catch (\InvalidArgumentException $e) {
            // Логируем ошибки валидации и выбрасываем исключение
            $this->logger->error("Validation failed for updating country image: " . $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            // Логируем общую ошибку
            $this->logger->error("An unexpected error occurred while updating country image: " . $e->getMessage());
            throw new \RuntimeException("Unable to update country image at this time.", 0, $e);
        }
    }

    //Обновление переводов у Страны
    public function updateCountryTranslation(int $countryId, int $translationId, array $data): array
    {
        $this->logger->info("Updating translation for Country ID: $countryId and Translation ID: $translationId");

        try {
            $country = $this->countriesValidationService->validateCountryExists($countryId);

            // Поиск перевода по ID
            $translation = $this->translationRepository->find($translationId);
            if (!$translation || $translation->getCountryID()->getCountryID() !== $countryId) {
                $this->logger->error("Translation for Country ID $countryId and Translation ID $translationId not found.");
                throw new \InvalidArgumentException("Translation with this ID does not exist for this country.");
            }

            // Проверка, что LanguageID не был передан в запросе
            $this->languagesValidationService->checkImmutableLanguageID($data, $translationId);

            // Проверка наличия обязательных полей и их значений (в данных или в объекте)
            $requiredFields = ['CountryName'];
            foreach ($requiredFields as $field) {
                $value = $data[$field] ?? $translation->{'get' . $field}();
                if (empty($value)) {
                    throw new \InvalidArgumentException("Field '$field' is required and cannot be empty.");
                }
            }
            // Валидация всех данных, переданных в $data
            $this->countriesValidationService->validateCountryTranslationData($data);

            // Обновление поля CountryName с проверкой уникальности и наличия
            FieldUpdateHelper::updateFieldIfPresent(
                $translation,
                $data,
                'CountryName',
                function ($newName) use ($translationId) {
                    $this->countriesValidationService->ensureUniqueCountryName($newName, $translationId);
                }
            );

            // Обновление полей перевода, игнорируя LanguageID
            foreach ($data as $field => $value) {
                $setter = 'set' . ucfirst($field);
                if (method_exists($translation, $setter) && !in_array($field, ['CountryID'])) {
                    $translation->$setter($value);
                } elseif (!in_array($field, ['CountryID'])) {
                    throw new \InvalidArgumentException("Field '$field' does not exist on CountryTranslation entity.");
                }
            }
            $this->helper->validateAndFilterFields($translation, $data);//проверяем список разрешенных полей
            $this->translationRepository->saveCountryTranslation($translation, true);
            $this->logger->info("Translation updated successfully for Country ID: $countryId and Translation ID: $translationId");

            return [
                'country' => $this->countriesValidationService->formatCountryData($country),
                'translation' => $this->countriesValidationService->formatCountryTranslationData($translation),
                'message' => 'Country translation updated successfully.'
            ];
        } catch (\InvalidArgumentException $e) {
            $this->logger->error("Validation failed for Country ID $countryId and Translation ID $translationId: " . $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error("An unexpected error occurred while updating translation for Country ID $countryId and Translation ID $translationId: " . $e->getMessage());
            throw new \RuntimeException("Unable to update country translation", 0, $e);
        }
    }

    /**
     * Удаление перевода страны.
     *
     * @param int $countryId
     * @param int $translationId
     * @return array
     */
    public function deleteCountryTranslation(int $countryId, int $translationId): array
    {
        $this->logger->info("Deleting translation with ID: $translationId for Country ID: $countryId");

        try {
            // Проверка существования страны
            $this->countriesValidationService->validateCountryExists($countryId);

            // Проверка существования перевода для данной страны
            $translation = $this->translationRepository->find($translationId);
            if (!$translation || $translation->getCountryID()->getCountryID() !== $countryId) {
                $this->logger->error("Translation with ID $translationId does not exist for Country ID $countryId.");
                throw new \InvalidArgumentException("Translation with this ID does not exist for this country.");
            }

            // Удаление перевода
            $this->translationRepository->deleteCountryTranslation($translation, true);
            $this->logger->info("Translation with ID $translationId successfully deleted for Country ID $countryId.");

            return [
                'message' => "Translation with ID $translationId successfully deleted for Country ID $countryId.",
            ];
        } catch (\InvalidArgumentException $e) {
            $this->logger->warning($e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error("An unexpected error occurred while deleting translation with ID $translationId for Country ID $countryId: " . $e->getMessage());
            throw new \RuntimeException("Unable to delete country translation", 0, $e);
        }
    }


    /**
     * Удаление Страны.
     *
     * @param int $countryId
     * @return array
     */
    public function deleteCountry(int $countryId): array
    {
        try {
            $this->logger->info("Executing deleteCountry method for ID: $countryId");

            $country = $this->countriesValidationService->validateCountryExists($countryId);


            // Удаляем переводы страны
            $translations = $this->translationRepository->findTranslationsByCountry($country);
            foreach ($translations as $translation) {
                $this->translationRepository->deleteCountryTranslation($translation, true);
            }

            // Удаляем саму страну
            $this->countryRepository->deleteCountry($country, true);
            $this->logger->info("Country with ID $countryId and its translations successfully deleted.");

            return [
                'message' => "Country with ID $countryId and its translations successfully deleted.",
            ];
        } catch (\InvalidArgumentException $e) {
            $this->logger->warning($e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error("An unexpected error occurred while deleting country with ID $countryId: " . $e->getMessage());
            throw $e;
        }
    }


    //Методы заполнения демо данными
    public function seedCountries(): array
    {
        $this->logger->info("Executing seedCountries method.");

        // Предустановленные данные стран
        $countriesData = [
            ["CountryLink" => "russia"],
            ["CountryLink" => "uae"]
        ];

        $createdCountries = [];

        foreach ($countriesData as $countryData) {
            try {
                // Валидация и проверка уникальности CountryLink
                $this->countriesValidationService->validateCountryLink($countryData);
                $this->countriesValidationService->ensureUniqueCountryLink($countryData['CountryLink']);

                // Создание новой страны
                $country = new Countries();
                $country->setCountryLink($countryData['CountryLink']);
                $this->countryRepository->saveCountry($country, true);

                $createdCountries[] = $this->countriesValidationService->formatCountryData($country);
                $this->logger->info("Country '{$countryData['CountryLink']}' created successfully.");
            } catch (\InvalidArgumentException $e) {
                $this->logger->warning("Country '{$countryData['CountryLink']}' already exists or invalid. Skipping.");
            } catch (\Exception $e) {
                $this->logger->error("Unexpected error creating country '{$countryData['CountryLink']}': " . $e->getMessage());
            }
        }

        return $createdCountries;
    }

    public function seedTranslations(): array
    {
        $this->logger->info("Executing seedTranslations method.");

        // Предустановленные данные переводов, ссылающиеся на CountryID
        $translationsData = [
            1 => [ // Предполагается, что CountryID 1 соответствует "first"
                [
                    "CountryName" => "Россия",
                    "CountryDescription" => "Описание для страны Russia",
                    "LanguageID" => 2
                ],
                [
                    "CountryName" => "Russia",
                    "CountryDescription" => "Description for Russia country",
                    "LanguageID" => 1
                ]
            ],
            2 => [ // Предполагается, что CountryID 2 соответствует "second"
                [
                    "CountryName" => "ОАЭ",
                    "CountryDescription" => "Описание для страны UAE",
                    "LanguageID" => 2
                ],
                [
                    "CountryName" => "UAE",
                    "CountryDescription" => "Description for UAE country",
                    "LanguageID" => 1
                ]
            ]
        ];

        $createdTranslations = [];

        foreach ($translationsData as $countryID => $translations) {
            // Ищем страну по CountryID
            try {
                $country = $this->countriesValidationService->validateCountryExists($countryID);
            } catch (\InvalidArgumentException $e) {
                $this->logger->warning("Country with ID $countryID not found. Skipping translations.");
                continue;
            }

            foreach ($translations as $translationData) {
                try {
                    // Проверка языка
                    $language = $this->languagesValidationService->validateLanguageID($translationData['LanguageID']);
                    // Проверка уникальности перевода
                    $this->countriesValidationService->ensureUniqueTranslation($country, $language);

                    // Создание и сохранение перевода
                    $translation = new CountryTranslation();
                    $translation->setCountryID($country);
                    $translation->setLanguageID($language);
                    $translation->setCountryName($translationData['CountryName']);
                    $translation->setCountryDescription($translationData['CountryDescription']);

                    $this->translationRepository->saveCountryTranslation($translation, true);
                    $createdTranslations[] = $this->countriesValidationService->formatCountryTranslationData($translation);

                    $this->logger->info("Translation for CountryID '{$countryID}' and LanguageID '{$language->getLanguageID()}' created successfully.");
                } catch (\Exception $e) {
                    $this->logger->error("Failed to add translation for CountryID '$countryID': " . $e->getMessage());
                }
            }
        }

        return $createdTranslations;
    }

    public function seedCountriesAndTranslations(): array
    {
        $this->logger->info("Executing combined seedCountriesAndTranslations method.");

        // Сначала создаем страны
        $createdCountries = $this->seedCountries();

        // Затем создаем переводы для созданных стран
        $createdTranslations = $this->seedTranslations();

        return [
            'countries' => $createdCountries,
            'translations' => $createdTranslations,
            'message' => 'Countries and translations seeded successfully.'
        ];
    }



}
