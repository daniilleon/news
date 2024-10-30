<?php

namespace Module\Languages\Controller\Api;

use Exception;
use InvalidArgumentException;
use Module\Languages\Service\LanguagesService;
use Module\Common\Factory\ResponseFactory;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api')]
class LanguagesController
{
    private LanguagesService $languagesService;
    private LoggerInterface $logger;
    private ResponseFactory $responseFactory;

    public function __construct(LanguagesService $languagesService, LoggerInterface $logger, ResponseFactory $responseFactory)
    {
        $this->languagesService = $languagesService;
        $this->logger = $logger;
        $this->responseFactory = $responseFactory;
    }

    // Получение списка всех языков
    #[Route('/languages', name: 'api_get_languages', methods: ['GET'])]
    public function getLanguages(): JsonResponse
    {
        $this->logger->info("Executing getLanguages method.");
        try {
            $languages = $this->languagesService->getAllLanguages();

            if (empty($languages)) {
                $defaultLanguage = $this->languagesService->addLanguage('EN', 'English');
                $languages = [$this->languagesService->formatLanguageData($defaultLanguage)];
                $this->logger->info("No languages found. Default language added.");

                return $this->responseFactory->createCreatedResponse([
                    'message' => 'No languages found. Added default language.',
                    'languages' => $languages,
                ]);
            }

            $this->logger->info("Languages retrieved successfully.");
            return $this->responseFactory->createSuccessResponse(['languages' => $languages]);
        } catch (InvalidArgumentException $e) {
            $this->logger->error($e->getMessage());
            return $this->responseFactory->createErrorResponse($e->getMessage(), JsonResponse::HTTP_BAD_REQUEST);
        } catch (Exception $e) {
            $this->logger->error("Unable to fetch languages: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to fetch languages', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Получение языка по его ID
    #[Route('/languages/{id}', name: 'api_get_language', methods: ['GET'])]
    public function getLanguage(int $id): JsonResponse
    {
        $this->logger->info("Executing getLanguage method for ID: $id");
        try {
            $language = $this->languagesService->getLanguageById($id);

            if (!$language) {
                $this->logger->warning("Language with ID $id not found.");
                return $this->responseFactory->createNotFoundResponse("Language with ID $id not found.");
            }

            $data = $this->languagesService->formatLanguageData($language);
            $this->logger->info("Successfully fetched language data for ID: $id.");
            return $this->responseFactory->createSuccessResponse($data);
        } catch (InvalidArgumentException $e) {
            return $this->responseFactory->createErrorResponse($e->getMessage(), JsonResponse::HTTP_BAD_REQUEST);
        } catch (Exception $e) {
            $this->logger->error("Unable to fetch language: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to fetch language', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Добавление нового языка
    #[Route('/languages/add', name: 'api_add_language', methods: ['POST'])]
    public function addLanguage(Request $request): JsonResponse
    {
        $this->logger->info("Executing addLanguage method.");
        try {
            $data = json_decode($request->getContent(), true);
            $languageCode = $data['LanguageCode'] ?? null;
            $languageName = $data['LanguageName'] ?? null;

            if (!$languageCode || !$languageName) {
                $this->logger->warning("Invalid data: Both LanguageCode and LanguageName are required.");
                return $this->responseFactory->createErrorResponse('Invalid data: LanguageCode and LanguageName are required', JsonResponse::HTTP_BAD_REQUEST);
            }

            $language = $this->languagesService->addLanguage($languageCode, $languageName);
            $languageData = $this->languagesService->formatLanguageData($language);
            $languageData['message'] = 'Language added successfully.';

            $this->logger->info("Language added successfully.");
            return $this->responseFactory->createCreatedResponse($languageData);
        } catch (InvalidArgumentException $e) {
            return $this->responseFactory->createErrorResponse($e->getMessage(), JsonResponse::HTTP_BAD_REQUEST);
        } catch (Exception $e) {
            $this->logger->error("Unable to add language: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to add language', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Обновление языка по его ID
    #[Route('/languages/update/{id}', name: 'api_update_language', methods: ['PUT'])]
    public function updateLanguage(int $id, Request $request): JsonResponse
    {
        $this->logger->info("Executing updateLanguage method for ID: $id");
        try {
            $data = json_decode($request->getContent(), true);

            if (!$data || empty($data)) {
                $this->logger->warning("No data provided for updating language with ID: $id.");
                return $this->responseFactory->createErrorResponse('No data provided for updating language', JsonResponse::HTTP_BAD_REQUEST);
            }

            $updatedLanguage = $this->languagesService->updateLanguage($id, $data);
            if (!$updatedLanguage) {
                $this->logger->warning("Language with ID $id not found for update.");
                return $this->responseFactory->createNotFoundResponse("Language with ID $id not found.");
            }

            $languageData = $this->languagesService->formatLanguageData($updatedLanguage);
            $languageData['message'] = 'Language updated successfully.';

            $this->logger->info("Language with ID $id updated successfully.");
            return $this->responseFactory->createSuccessResponse($languageData);
        } catch (InvalidArgumentException $e) {
            return $this->responseFactory->createErrorResponse($e->getMessage(), JsonResponse::HTTP_BAD_REQUEST);
        } catch (Exception $e) {
            $this->logger->error("Unable to update language: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to update language', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Удаление языка по его ID
    #[Route('/languages/delete/{id}', name: 'api_delete_language', methods: ['DELETE'])]
    public function deleteLanguage(int $id): JsonResponse
    {
        $this->logger->info("Executing deleteLanguage method for ID: $id");
        try {
            $deleted = $this->languagesService->deleteLanguage($id);

            if ($deleted) {
                $this->logger->info("Language with ID $id deleted successfully.");
                return $this->responseFactory->createSuccessResponse([
                    'message' => "Language with ID $id successfully deleted."
                ]);
            } else {
                $this->logger->warning("Language with ID $id not found for deletion.");
                return $this->responseFactory->createNotFoundResponse("Language with ID $id not found.");
            }
        } catch (InvalidArgumentException $e) {
            return $this->responseFactory->createErrorResponse($e->getMessage(), JsonResponse::HTTP_BAD_REQUEST);
        } catch (Exception $e) {
            $this->logger->error("Unable to delete language: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to delete language', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
