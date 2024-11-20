<?php

namespace Module\Core\Countries\Service;

use Module\Common\Helpers\FieldUpdateHelper;
use Module\Common\Service\ImageService;
use Module\Core\Countries\Entity\Countries;
use Module\Core\Countries\Entity\CountryTranslations;
use Module\Core\Countries\Repository\CountriesRepository;
use Module\Core\Countries\Repository\CountryTranslationsRepository;
use \Module\Core\Countries\Service\CountriesValidationService;
use Module\Common\Proxy\Core\LanguagesProxyService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class CountriesService
{
    private CountriesRepository $countryRepository;
    private CountryTranslationsRepository $translationRepository;
    private LanguagesProxyService $languagesProxyService;
    private CountriesValidationService $countriesValidationService;
    private LoggerInterface $logger;
    private ImageService $imageService;
    private FieldUpdateHelper $helper;

    public function __construct(
        CountriesRepository           $countryRepository,
        CountryTranslationsRepository $translationRepository,
        LanguagesProxyService         $languagesProxyService,
        CountriesValidationService    $countriesValidationService,
        ImageService                  $imageService,
        FieldUpdateHelper             $helper,
        LoggerInterface               $logger
    ) {
        $this->countryRepository = $countryRepository;
        $this->translationRepository = $translationRepository;
        $this->languagesProxyService = $languagesProxyService;
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
            'translations' => array_map([$this->countriesValidationService, 'formatCountryTranslationsData'], $translations),
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
            $languageData = $this->languagesProxyService->validateLanguageID($data['LanguageID']  ?? null);

            // Проверка уникальности перевода
            $this->countriesValidationService->ensureUniqueTranslation($country, $languageData['LanguageID']);

            // Создание нового перевода
            $translation = new CountryTranslations();
            $this->helper->validateAndFilterFields($translation, $data);//проверяем список разрешенных полей
            $translation->setCountryID($country);
            $translation->setLanguageID($languageData['LanguageID']);

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
            $this->translationRepository->saveCountryTranslations($translation, true);
            $this->logger->info("Translation for Country ID $countryId created successfully.");

            return [
                'country' => $this->countriesValidationService->formatCountryData($country),
                'translation' => $this->countriesValidationService->formatCountryTranslationsData($translation),
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
            $this->languagesProxyService->checkImmutableLanguageID($data, $translation->getLanguageID());

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
            $this->translationRepository->saveCountryTranslations($translation, true);
            $this->logger->info("Translation updated successfully for Country ID: $countryId and Translation ID: $translationId");

            return [
                'country' => $this->countriesValidationService->formatCountryData($country),
                'translation' => $this->countriesValidationService->formatCountryTranslationsData($translation),
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
            $this->translationRepository->deleteCountryTranslations($translation, true);
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
                $this->translationRepository->deleteCountryTranslations($translation, true);
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


    /*/Методы заполнения демо данными/*/
    public function seedCountriesAndTranslations(): array
    {
        $this->logger->info("Executing seedJobTitlesAndTranslations method.");

        // Данные для предустановленных должностей
        $countriesData = [
            ["CountryLink" => "russia"],
            ["CountryLink" => "uae"]
        ];

        $createdCountries = [];
        $countryIds = [];

        // Создаём должности и сохраняем их ID
        foreach ($countriesData as $countryData) {
            try {
                $this->countriesValidationService->validateCountryLink($countryData);
                $this->countriesValidationService->ensureUniqueCountryLink($countryData['CountryLink']);

                $country = new Countries();
                $country->setCountryLink($countryData['CountryLink']);
                $this->countryRepository->saveCountry($country, true);

                $createdCountries[] = $this->countriesValidationService->formatCountryData($country);
                $countryIds[$countryData['CountryLink']] = $country->getCountryID(); // Сохраняем ID должности

                $this->logger->info("Country Link '{$countryData['CountryLink']}' created successfully.");
            } catch (\InvalidArgumentException $e) {
                $this->logger->warning("Country Link '{$countryData['CountryLink']}' already exists or is invalid. Skipping.");
            }
        }

        // Данные для переводов категорий, привязанные к CountryID
        $translationsData = [
                $countryIds['russia'] ?? null => [
                    ["CountryName" => "Россия", "CountryDescription" => "Описание для страны Russia", "LanguageID" => 2],
                    ["CountryName" => "Russia", "CountryDescription" => "Description for Russia country", "LanguageID" => 1]
                ],
                $countryIds['uae'] ?? null => [
                    ["CountryName" => "ОАЭ", "CountryDescription" => "Описание для страны UAE", "LanguageID" => 2],
                    ["CountryName" => "UAE", "CountryDescription" => "Description for UAE country", "LanguageID" => 1]
                ],
        ];

        $createdTranslations = [];

        // Создаём переводы для каждой должности, используя их ID
        foreach ($translationsData as $categoryIds => $translations) {
            if (!$categoryIds) {
                continue; // Пропускаем, если ID не найден
            }

            $country = $this->countryRepository->findCountryById($categoryIds);

            foreach ($translations as $translationData) {
                try {
                    $languageData = $this->languagesProxyService->validateLanguageID($translationData['LanguageID']);
                    $languageId = $languageData['LanguageID'];
                    $this->countriesValidationService->ensureUniqueTranslation($country, $languageId);

                    $translation = new CountryTranslations();
                    $translation->setCountryID($country);
                    $translation->setLanguageID($languageId);
                    $translation->setCountryName($translationData['CountryName']);
                    $translation->setCountryDescription($translationData['CountryDescription']);

                    $this->translationRepository->saveCountryTranslations($translation, true);
                    $createdTranslations[] = $this->countriesValidationService->formatCountryTranslationsData($translation);

                    $this->logger->info("Translation for Country ID '{$categoryIds}' and LanguageID '{$languageId}' created successfully.");
                } catch (\Exception $e) {
                    $this->logger->error("Failed to add translation for Country ID '$categoryIds': " . $e->getMessage());
                }
            }
        }

        return [
            'country' => $createdCountries,
            'translations' => $createdTranslations,
            'message' => 'Country and translations seeded successfully.'
        ];
    }

}
