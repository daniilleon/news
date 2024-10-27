<?php

namespace Module\Languages\Controller\Api;

use Module\Languages\Service\LanguageService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api')]
class LanguageController
{
    private LanguageService $languageService;
    private LoggerInterface $logger;

    public function __construct(LanguageService $languageService, LoggerInterface $logger)
    {
        $this->languageService = $languageService;
        $this->logger = $logger;
        $this->logger->info("LanguageController instance created.");
    }

    // Получение списка всех языков
    #[Route('/languages', name: 'api_get_languages', methods: ['GET'])]
    public function getLanguages(): JsonResponse
    {
        $this->logger->info("Executing getLanguages method.");

        try {
            $languages = $this->languageService->getAllLanguages();

            if (empty($languages)) {
                $this->logger->info("No languages found.");
                // Добавление нового языка по умолчанию, если список пуст
                $defaultLanguage = $this->languageService->addLanguage('EN', 'English');
                $this->logger->info("Added default language.", [
                    'id' => $defaultLanguage->getId(),
                    'code' => $defaultLanguage->getCode(),
                    'name' => $defaultLanguage->getName()
                ]);
                return new JsonResponse(
                    [
                        'message' => 'No languages found. Added default language.',
                        'language' => [
                            'id' => $defaultLanguage->getId(),
                            'code' => $defaultLanguage->getCode(),
                            'name' => $defaultLanguage->getName(),
                        ]
                    ],
                    JsonResponse::HTTP_CREATED
                );
            }

            // Преобразуем объекты Language в массивы перед отправкой
            $languagesArray = array_map(function($language) {
                return [
                    'id' => $language->getId(),
                    'code' => $language->getCode(),
                    'name' => $language->getName(),
                ];
            }, $languages);

            $this->logger->info("Returning list of languages.", ['languages' => $languagesArray]);
            return new JsonResponse($languagesArray, JsonResponse::HTTP_OK);
        } catch (\Exception $e) {
            $this->logger->error("Error in getLanguages method: " . $e->getMessage(), ['exception' => $e]);
            return new JsonResponse(['error' => 'Unable to fetch languages'], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Получение одного языка по ID
    #[Route('/languages/{id}', name: 'api_get_id_language', methods: ['GET'])]
    public function getLanguage(int $id): JsonResponse
    {
        $this->logger->info("Executing getLanguage method for ID: $id");

        try {
            $language = $this->languageService->getLanguageById($id);

            if (!$language) {
                $this->logger->info("Language with ID $id not found.");
                return new JsonResponse(['error' => "Language with ID $id not found."], JsonResponse::HTTP_NOT_FOUND);
            }

            // Преобразуем объект Language в массив перед возвратом
            $data = [
                'id' => $language->getId(),
                'code' => $language->getCode(),
                'name' => $language->getName()
            ];

            return new JsonResponse($data, JsonResponse::HTTP_OK);
        } catch (\Exception $e) {
            $this->logger->error("Error in getLanguage method: " . $e->getMessage(), ['exception' => $e]);
            return new JsonResponse(['error' => 'Unable to fetch language.'], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Добавление нового языка
    #[Route('/languages/add', name: 'api_add_language', methods: ['POST'])]
    public function addLanguage(Request $request): JsonResponse
    {
        $this->logger->info("Executing addLanguage method.");

        try {
            $data = json_decode($request->getContent(), true);
            $code = $data['code'] ?? null;
            $name = $data['name'] ?? null;

            if (!$code || !$name) {
                $this->logger->warning("Invalid data for adding language.", ['data' => $data]);
                return new JsonResponse(['error' => 'Invalid data'], JsonResponse::HTTP_BAD_REQUEST);
            }

            // Добавляем язык через сервис
            $language = $this->languageService->addLanguage($code, $name);

            // Если язык не добавился (например, уже существует)
            if ($language === null) {
                $this->logger->warning("Language with code {$code} already exists.");
                return new JsonResponse(['error' => 'Language already exists'], JsonResponse::HTTP_CONFLICT);
            }

            // Если добавление прошло успешно
            $this->logger->info("Language added successfully.", [
                'id' => $language->getId(),
                'code' => $language->getCode(),
                'name' => $language->getName()
            ]);
            return new JsonResponse(
                [
                    'id' => $language->getId(),
                    'code' => $language->getCode(),
                    'name' => $language->getName(),
                    'message' => 'Language added successfully.'
                ],
                JsonResponse::HTTP_CREATED
            );
        } catch (\InvalidArgumentException $e) {
            $this->logger->error("Validation error in addLanguage method: " . $e->getMessage());
            return new JsonResponse(['error' => $e->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            $this->logger->error("Error in addLanguage method: " . $e->getMessage(), ['exception' => $e]);
            return new JsonResponse(['error' => 'Unable to add language'], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Удаление языка по ID
    #[Route('/languages/delete/{id}', name: 'api_delete_language', methods: ['DELETE'])]
    public function deleteLanguage(int $id): JsonResponse
    {
        $this->logger->info("Executing deleteLanguage method for ID: $id");

        try {
            $deleted = $this->languageService->deleteLanguage($id);

            if ($deleted) {
                $this->logger->info("Language with ID $id successfully deleted.");
                return new JsonResponse(['message' => "Language with ID $id successfully deleted."], JsonResponse::HTTP_OK);
            } else {
                $this->logger->warning("Language with ID $id not found for deletion.");
                return new JsonResponse(['error' => "Language with ID $id not found."], JsonResponse::HTTP_NOT_FOUND);
            }
        } catch (\Exception $e) {
            $this->logger->error("Error in deleteLanguage method: " . $e->getMessage(), ['exception' => $e]);
            return new JsonResponse(['error' => 'Unable to delete language.'], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Обновление языка с логированием и обработкой исключений
    #[Route('/languages/update/{id}', name: 'api_update_language', methods: ['PUT'])]
    public function updateLanguage(int $id, Request $request): JsonResponse
    {
        $this->logger->info("Executing updateLanguage method for ID: $id");

        try {
            $data = json_decode($request->getContent(), true);

            if (!$data || empty($data)) {
                $this->logger->warning("No data provided for updating language.", ['data' => $data]);
                return new JsonResponse(['error' => 'No data provided for updating language'], JsonResponse::HTTP_BAD_REQUEST);
            }

            $updatedLanguage = $this->languageService->updateLanguage($id, $data);

            if (!$updatedLanguage) {
                $this->logger->warning("Language with ID $id not found for update.");
                return new JsonResponse(['error' => "Language with ID $id not found."], JsonResponse::HTTP_NOT_FOUND);
            }

            // Преобразуем объект Language в массив перед возвратом
            $updatedData = [
                'id' => $updatedLanguage->getId(),
                'code' => $updatedLanguage->getCode(),
                'name' => $updatedLanguage->getName()
            ];

            $this->logger->info("Language with ID $id updated successfully.", ['language' => $updatedData]);
            return new JsonResponse(
                [
                    'message' => 'Language updated successfully.',
                    'language' => $updatedData
                ],
                JsonResponse::HTTP_OK
            );
        } catch (\InvalidArgumentException $e) {
            $this->logger->error("Validation error in updateLanguage method: " . $e->getMessage());
            return new JsonResponse(['error' => $e->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            $this->logger->error("Error in updateLanguage method: " . $e->getMessage(), ['exception' => $e]);
            return new JsonResponse(['error' => 'Unable to update language.'], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


}
