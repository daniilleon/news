<?php

namespace Module\Persons\EducationLevels\Controller\Api;

use Module\Persons\EducationLevels\Service\EducationLevelsService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Module\Common\Factory\ResponseFactory;

//Контроллер API для работы с должностями.
#[Route('/api/persons/educationlevels', name: 'api_persons_educationlevels_')]
class EducationLevelsController
{
    private EducationLevelsService $educationLevelsService;
    private LoggerInterface $logger;
    private ResponseFactory $responseFactory;

    public function __construct(
        EducationLevelsService $educationLevelsService,
        LoggerInterface          $logger,
        ResponseFactory          $responseFactory
    ) {
        $this->educationLevelsService = $educationLevelsService;
        $this->logger = $logger;
        $this->responseFactory = $responseFactory;
    }

    // Получение списка всех Должностей.education_levels
    #[Route('/', name: 'get_all', methods: ['GET'])]
    public function getEducationLevels(): JsonResponse
    {
        try {
            $this->logger->info("Executing getEducationLevels method.");
            $educationLevelsData = $this->educationLevelsService->getAllEducationLevels();
            return $this->responseFactory->createSuccessResponse($educationLevelsData);
        } catch (\Exception $e) {
            $this->logger->error("Failed to fetch EducationLevels: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to fetch EducationLevels', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Получение данных Должности по её ID.
    #[Route('/{educationLevelId}', name: 'get_id', methods: ['GET'])]
    public function getEducationLevelId(int $educationLevelId): JsonResponse
    {
        try {
            $this->logger->info("Executing getEducationLevel method.");
            $educationLevelsData = $this->educationLevelsService->getEducationLevelById($educationLevelId);
            return $this->responseFactory->createSuccessResponse($educationLevelsData);
        } catch (\InvalidArgumentException $e) {
            $this->logger->warning($e->getMessage());
            return $this->responseFactory->createNotFoundResponse($e->getMessage());
        } catch (\Exception $e) {
            $this->logger->error("Failed to fetch EducationLevel with ID $educationLevelId: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to fetch EducationLevel', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Создание новой должности (её кода).
    #[Route('/add', name: 'add', methods: ['POST'])]
    public function addEducationLevel(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $responseData = $this->educationLevelsService->createEducationLevel($data);
            return $this->responseFactory->createCreatedResponse($responseData);
        } catch (\InvalidArgumentException $e) {
            $this->logger->error("InvalidArgumentException caught in controller: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse($e->getMessage(), JsonResponse::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            $this->logger->error("Failed to add EducationLevel: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to add EducationLevel', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Добавление перевода для должности.
    #[Route('/{educationLevelId}/add-translation', name: 'add_translation', methods: ['POST'])]
    public function addEducationLevelTranslation(int $educationLevelId, Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $responseData = $this->educationLevelsService->createEducationLevelTranslation($educationLevelId, $data);
            return $this->responseFactory->createCreatedResponse($responseData);
        } catch (\InvalidArgumentException $e) {
            return $this->responseFactory->createErrorResponse($e->getMessage(), JsonResponse::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            $this->logger->error("Failed to add translation for EducationLevel ID $educationLevelId: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to add EducationLevel translation', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Обновление только основной должности (кода)
    #[Route('/{educationLevelId}/update', name: 'update_id', methods: ['PUT'])]
    public function updateEducationLevelCode(int $educationLevelId, Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $responseData = $this->educationLevelsService->updateEducationLevelCode($educationLevelId, $data);
            return $this->responseFactory->createSuccessResponse($responseData);
        } catch (\InvalidArgumentException $e) {
            return $this->responseFactory->createErrorResponse($e->getMessage(), JsonResponse::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            $this->logger->error("Failed to update EducationLevel code for ID $educationLevelId: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to update EducationLevel code', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Обновление перевода должности для указанного языка
    #[Route('/{educationLevelId}/update-translation/{translationId}', name: 'update_translation', methods: ['PUT'])]
    public function updateEducationLevelTranslation(int $educationLevelId, int $translationId, Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $responseData = $this->educationLevelsService->updateEducationLevelTranslation($educationLevelId, $translationId, $data);
            return $this->responseFactory->createSuccessResponse($responseData);
        } catch (\InvalidArgumentException $e) {
            return $this->responseFactory->createErrorResponse($e->getMessage(), JsonResponse::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            $this->logger->error("Failed to update translation for EducationLevel ID $educationLevelId and language ID $translationId: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to update EducationLevel translation', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Удаление конкретного перевода должности по его ID.
    #[Route('/{educationLevelId}/delete-translation/{translationId}', name: 'delete_translation', methods: ['DELETE'])]
    public function deleteEducationLevelTranslation(int $educationLevelId, int $translationId): JsonResponse
    {
        try {
            $result = $this->educationLevelsService->deleteEducationLevelTranslation($educationLevelId, $translationId);
            return $this->responseFactory->createSuccessResponse($result);
        } catch (\InvalidArgumentException $e) {
            return $this->responseFactory->createNotFoundResponse($e->getMessage());
        } catch (\Exception $e) {
            $this->logger->error("Failed to delete translation with ID $translationId for EducationLevel ID $educationLevelId: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to delete EducationLevel translation', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Удаление должности по ее ID.
    #[Route('/{educationLevelId}/delete', name: 'delete_id', methods: ['DELETE'])]
    public function deleteEducationLevel(int $educationLevelId): JsonResponse
    {
        try {
            $result = $this->educationLevelsService->deleteEducationLevel($educationLevelId);
            return $this->responseFactory->createSuccessResponse($result);
        } catch (\InvalidArgumentException $e) {
            return $this->responseFactory->createNotFoundResponse($e->getMessage());
        } catch (\Exception $e) {
            $this->logger->error("Failed to delete EducationLevel with ID $educationLevelId: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to delete EducationLevel', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    //Для демо данных
    #[Route('/seed', name: 'seed', methods: ['POST'])]
    public function seedEducationLevelAndTranslations(): JsonResponse
    {
        try {
            $result = $this->educationLevelsService->seedEducationLevelAndTranslations();
            return $this->responseFactory->createCreatedResponse($result);
        } catch (\Exception $e) {
            $this->logger->error("Failed to seed EducationLevel titles and translations: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to seed EducationLevel titles and translations', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


}
