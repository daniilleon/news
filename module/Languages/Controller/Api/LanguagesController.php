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

#[Route('/api/languages')]
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
    // Получение списка всех языков.
    #[Route('/', name: 'api_get_languages', methods: ['GET'])]
    public function getLanguages(): JsonResponse
    {
        try {
            $this->logger->info("Executing getLanguages method.");
            $languagesData = $this->languagesService->getAllLanguages();
            return $this->responseFactory->createSuccessResponse($languagesData);
        } catch (\InvalidArgumentException $e) {
            $this->logger->warning("Validation error: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse($e->getMessage(), JsonResponse::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            $this->logger->error("Failed to fetch languages: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to fetch languages', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    // Получение данных языка по его ID
    #[Route('/{id}', name: 'api_get_language', methods: ['GET'])]
    public function getLanguage(int $id): JsonResponse
    {
        try {
            $languageData = $this->languagesService->getLanguageById($id);
            return $this->responseFactory->createSuccessResponse($languageData);
        } catch (\InvalidArgumentException $e) {
            return $this->responseFactory->createNotFoundResponse($e->getMessage());
        } catch (\Exception $e) {
            return $this->responseFactory->createErrorResponse('Unable to fetch language', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    // Добавление нового языка.
    #[Route('/add', name: 'api_add_language', methods: ['POST'])]
    public function addLanguage(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $languageData = $this->languagesService->addLanguage($data);

            return $this->responseFactory->createCreatedResponse($languageData);
        } catch (\InvalidArgumentException $e) {
            $this->logger->error("Invalid data for adding language: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse($e->getMessage(), JsonResponse::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            $this->logger->error("Failed to add language: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to add language', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Обновление языка по его ID
    #[Route('/{id}/update', name: 'api_update_language', methods: ['PUT'])]
    public function updateLanguage(int $id, Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $responseData = $this->languagesService->updateLanguage($id, $data);
            return $this->responseFactory->createSuccessResponse($responseData);
        } catch (\InvalidArgumentException $e) {
            return $this->responseFactory->createErrorResponse($e->getMessage(), JsonResponse::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            $this->logger->error("Failed to update language with ID $id: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to update language', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Удаление языка по его ID
    #[Route('/{id}/delete', name: 'api_delete_language', methods: ['DELETE'])]
    public function deleteLanguage(int $id): JsonResponse
    {
        try {
            $result = $this->languagesService->deleteLanguage($id);
            return $this->responseFactory->createSuccessResponse($result);
        } catch (\InvalidArgumentException $e) {
            return $this->responseFactory->createNotFoundResponse($e->getMessage());
        } catch (\Exception $e) {
            $this->logger->error("Failed to delete language with ID $id: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to delete language', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/seed', name: 'api_seed_languages', methods: ['POST'])]
    public function seedLanguages(): JsonResponse
    {
        try {
            $addedLanguages = $this->languagesService->seedLanguages();

            if (empty($addedLanguages)) {
                return $this->responseFactory->createErrorResponse('No new languages were added. All languages already exist.', JsonResponse::HTTP_CONFLICT);
            }

            return $this->responseFactory->createCreatedResponse($addedLanguages);
        } catch (\Exception $e) {
            $this->logger->error("Failed to seed languages: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to seed languages', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


}
