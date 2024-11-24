<?php

namespace Module\Shared\MissionsStatements\Controller\Api;

use Module\Shared\MissionsStatements\Service\MissionsStatementsService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Module\Common\Factory\ResponseFactory;

// Основной контроллер API для работы с категориями.
#[Route('/api/shared/missionstatement', name: 'api_missionstatement_')]
class MissionsStatementsController
{
    private MissionsStatementsService $missionStatementService;
    private LoggerInterface $logger;
    private ResponseFactory $responseFactory;

    public function __construct(
        MissionsStatementsService $missionStatementService,
        LoggerInterface $logger,
        ResponseFactory $responseFactory
    ) {
        $this->missionStatementService = $missionStatementService;
        $this->logger = $logger;
        $this->responseFactory = $responseFactory;
    }

    // Получение списка всех категорий.
    #[Route('/', name: 'get_all', methods: ['GET'])]
    public function getAllMissionsStatements(): JsonResponse
    {
        try {
            $this->logger->info("Executing getMissionsStatements method.");
            $missionStatementData = $this->missionStatementService->getAllmissionStatement();
            return $this->responseFactory->createSuccessResponse($missionStatementData);
        } catch (\Exception $e) {
            $this->logger->error("Failed to fetch MissionStatement: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to fetch MissionStatement', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Получение данных категории по ее ID.
    #[Route('/{missionStatementId}', name: 'get_id', methods: ['GET'])]
    public function getIdMissionStatement(int $missionStatementId): JsonResponse
    {
        try {
            $this->logger->info("Executing getmissionStatement method.");
            $missionStatementData = $this->missionStatementService->getMissionStatementById($missionStatementId);
            return $this->responseFactory->createSuccessResponse($missionStatementData);
        } catch (\InvalidArgumentException $e) {
            $this->logger->warning($e->getMessage());
            return $this->responseFactory->createNotFoundResponse($e->getMessage());
        } catch (\Exception $e) {
            $this->logger->error("Failed to fetch MissionStatement with ID $missionStatementId: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to fetch MissionStatement', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Создание новой категории.
    #[Route('/add', name: 'add', methods: ['POST'])]
    public function addMissionStatement(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $responseData = $this->missionStatementService->createMissionStatement($data);
            return $this->responseFactory->createCreatedResponse($responseData);
        } catch (\InvalidArgumentException $e) {
            $this->logger->error("InvalidArgumentException caught in controller: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse($e->getMessage(), JsonResponse::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            $this->logger->error("Failed to add MissionStatement: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to add MissionStatement', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Добавление перевода для категории.
    #[Route('/{missionStatementId}/add-translation', name: 'add_translation', methods: ['POST'])]
    public function addMissionStatementTranslation(int $missionStatementId, Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $responseData = $this->missionStatementService->createMissionStatementTranslation($missionStatementId, $data);
            return $this->responseFactory->createCreatedResponse($responseData);
        } catch (\InvalidArgumentException $e) {
            return $this->responseFactory->createErrorResponse($e->getMessage(), JsonResponse::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            $this->logger->error("Failed to add translation for missionStatement ID $missionStatementId: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to add missionStatement translation', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Обновление только основной категории (ссылки)
    #[Route('/{missionStatementId}/update', name: 'update_id', methods: ['PUT'])]
    public function updateMissionStatementCode(int $missionStatementId, Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $responseData = $this->missionStatementService->updateMissionStatementCode($missionStatementId, $data);
            return $this->responseFactory->createSuccessResponse($responseData);
        } catch (\InvalidArgumentException $e) {
            return $this->responseFactory->createErrorResponse($e->getMessage(), JsonResponse::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            $this->logger->error("Failed to update MissionStatement Code for ID $missionStatementId: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to update MissionStatement Code', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Обновление перевода категории для указанного языка
    #[Route('/{missionStatementId}/update-translation/{translationId}', name: 'update_translation', methods: ['PUT'])]
    public function updateMissionStatementTranslation(int $missionStatementId, int $translationId, Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $responseData = $this->missionStatementService->updateMissionStatementTranslation($missionStatementId, $translationId, $data);
            return $this->responseFactory->createSuccessResponse($responseData);
        } catch (\InvalidArgumentException $e) {
            return $this->responseFactory->createErrorResponse($e->getMessage(), JsonResponse::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            $this->logger->error("Failed to update translation for MissionStatement ID $missionStatementId and language ID $translationId: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to update MissionStatement translation', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Удаление конкретного перевода категории по его ID.
    #[Route('/{missionStatementId}/delete-translation/{translationId}', name: 'delete_translation', methods: ['DELETE'])]
    public function deleteMissionStatementTranslation(int $missionStatementId, int $translationId): JsonResponse
    {
        try {
            $result = $this->missionStatementService->deleteMissionStatementTranslation($missionStatementId, $translationId);
            return $this->responseFactory->createSuccessResponse($result);
        } catch (\InvalidArgumentException $e) {
            return $this->responseFactory->createNotFoundResponse($e->getMessage());
        } catch (\Exception $e) {
            $this->logger->error("Failed to delete translation with ID $translationId for MissionStatement ID $missionStatementId: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to delete MissionStatement translation', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Удаление категории по ее ID.
    #[Route('/{id}/delete', name: 'delete_id', methods: ['DELETE'])]
    public function deleteMissionStatement(int $id): JsonResponse
    {
        try {
            $result = $this->missionStatementService->deleteMissionStatement($id);
            return $this->responseFactory->createSuccessResponse($result);
        } catch (\InvalidArgumentException $e) {
            return $this->responseFactory->createNotFoundResponse($e->getMessage());
        } catch (\Exception $e) {
            $this->logger->error("Failed to delete MissionStatement with ID $id: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to delete MissionStatement', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/seed', name: 'seed', methods: ['POST'])]
    public function seedMissionStatementAndTranslations(): JsonResponse
    {
        try {
            $result = $this->missionStatementService->seedMissionStatementAndTranslations();
            return $this->responseFactory->createCreatedResponse($result);
        } catch (\Exception $e) {
            $this->logger->error("Failed to seed MissionStatement and translations: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to seed MissionStatement and translations', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}
