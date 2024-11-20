<?php

namespace Module\Shared\RoleStatus\Controller\Api;

use Module\Shared\RoleStatus\Service\RoleStatusService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Module\Common\Factory\ResponseFactory;

//Контроллер API для работы с должностями.
#[Route('/api/rolestatus', name: 'api_rolestatus_')]
class RoleStatusController
{
    private RoleStatusService $roleStatusService;
    private LoggerInterface $logger;
    private ResponseFactory $responseFactory;

    public function __construct(
        RoleStatusService        $roleStatusService,
        LoggerInterface          $logger,
        ResponseFactory          $responseFactory
    ) {
        $this->roleStatusService = $roleStatusService;
        $this->logger = $logger;
        $this->responseFactory = $responseFactory;
    }

    // Получение списка всех Должностей.
    #[Route('/', name: 'get_all', methods: ['GET'])]
    public function getRoleStatus(): JsonResponse
    {
        try {
            $this->logger->info("Executing getRoleStatus method.");
            $roleStatusData = $this->roleStatusService->getAllRoleStatus();
            return $this->responseFactory->createSuccessResponse($roleStatusData);
        } catch (\Exception $e) {
            $this->logger->error("Failed to fetch RoleStatus: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to fetch RoleStatus', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Получение данных Должности по её ID.
    #[Route('/{roleStatusId}', name: 'get_id', methods: ['GET'])]
    public function getRoleStatusId(int $roleStatusId): JsonResponse
    {
        try {
            $this->logger->info("Executing getRoleStatus method.");
            $roleStatusData = $this->roleStatusService->getRoleStatusById($roleStatusId);
            return $this->responseFactory->createSuccessResponse($roleStatusData);
        } catch (\InvalidArgumentException $e) {
            $this->logger->warning($e->getMessage());
            return $this->responseFactory->createNotFoundResponse($e->getMessage());
        } catch (\Exception $e) {
            $this->logger->error("Failed to fetch RoleStatus with ID $roleStatusId: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to fetch RoleStatus', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Создание новой должности (её кода).
    #[Route('/add', name: 'add', methods: ['POST'])]
    public function addRoleStatus(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $responseData = $this->roleStatusService->createRoleStatus($data);
            return $this->responseFactory->createCreatedResponse($responseData);
        } catch (\InvalidArgumentException $e) {
            $this->logger->error("InvalidArgumentException caught in controller: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse($e->getMessage(), JsonResponse::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            $this->logger->error("Failed to add RoleStatus: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to add RoleStatus', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Добавление перевода для должности.
    #[Route('/{roleStatusId}/add-translation', name: 'add_translation', methods: ['POST'])]
    public function addRoleStatusTranslation(int $roleStatusId, Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $responseData = $this->roleStatusService->createRoleStatusTranslation($roleStatusId, $data);
            return $this->responseFactory->createCreatedResponse($responseData);
        } catch (\InvalidArgumentException $e) {
            return $this->responseFactory->createErrorResponse($e->getMessage(), JsonResponse::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            $this->logger->error("Failed to add translation for RoleStatus ID $roleStatusId: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to add RoleStatus translation', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Обновление только основной должности (кода)
    #[Route('/{roleStatusId}/update', name: 'update_id', methods: ['PUT'])]
    public function updateRoleStatusCode(int $roleStatusId, Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $responseData = $this->roleStatusService->updateRoleStatusCode($roleStatusId, $data);
            return $this->responseFactory->createSuccessResponse($responseData);
        } catch (\InvalidArgumentException $e) {
            return $this->responseFactory->createErrorResponse($e->getMessage(), JsonResponse::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            $this->logger->error("Failed to update RoleStatus code for ID $roleStatusId: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to update RoleStatus code', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Обновление перевода должности для указанного языка
    #[Route('/{roleStatusId}/update-translation/{translationId}', name: 'update_translation', methods: ['PUT'])]
    public function updateRoleStatusTranslation(int $roleStatusId, int $translationId, Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $responseData = $this->roleStatusService->updateRoleStatusTranslation($roleStatusId, $translationId, $data);
            return $this->responseFactory->createSuccessResponse($responseData);
        } catch (\InvalidArgumentException $e) {
            return $this->responseFactory->createErrorResponse($e->getMessage(), JsonResponse::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            $this->logger->error("Failed to update translation for RoleStatus ID $roleStatusId and language ID $translationId: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to update RoleStatus translation', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Удаление конкретного перевода должности по его ID.
    #[Route('/{roleStatusId}/delete-translation/{translationId}', name: 'delete_translation', methods: ['DELETE'])]
    public function deleteRoleStatusTranslation(int $roleStatusId, int $translationId): JsonResponse
    {
        try {
            $result = $this->roleStatusService->deleteRoleStatusTranslation($roleStatusId, $translationId);
            return $this->responseFactory->createSuccessResponse($result);
        } catch (\InvalidArgumentException $e) {
            return $this->responseFactory->createNotFoundResponse($e->getMessage());
        } catch (\Exception $e) {
            $this->logger->error("Failed to delete translation with ID $translationId for RoleStatus ID $roleStatusId: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to delete RoleStatus translation', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Удаление должности по ее ID.
    #[Route('/{roleStatusId}/delete/', name: 'delete_id', methods: ['DELETE'])]
    public function deleteRoleStatus(int $roleStatusId): JsonResponse
    {
        try {
            $result = $this->roleStatusService->deleteRoleStatus($roleStatusId);
            return $this->responseFactory->createSuccessResponse($result);
        } catch (\InvalidArgumentException $e) {
            return $this->responseFactory->createNotFoundResponse($e->getMessage());
        } catch (\Exception $e) {
            $this->logger->error("Failed to delete RoleStatus with ID $roleStatusId: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to delete RoleStatus', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    //Для демо данных
    #[Route('/seed', name: 'seed', methods: ['POST'])]
    public function seedRoleStatusAndTranslations(): JsonResponse
    {
        try {
            $result = $this->roleStatusService->seedRoleStatusAndTranslations();
            return $this->responseFactory->createCreatedResponse($result);
        } catch (\Exception $e) {
            $this->logger->error("Failed to seed RoleStatus titles and translations: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to seed RoleStatus titles and translations', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


}
