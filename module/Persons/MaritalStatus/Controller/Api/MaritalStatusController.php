<?php

namespace Module\Persons\MaritalStatus\Controller\Api;

use Module\Persons\MaritalStatus\Service\MaritalStatusService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Module\Common\Factory\ResponseFactory;

//Контроллер API для работы с должностями.
#[Route('/api/persons/maritalstatus', name: 'api_persons_maritalstatus_')]
class MaritalStatusController
{
    private MaritalStatusService $maritalStatusService;
    private LoggerInterface $logger;
    private ResponseFactory $responseFactory;

    public function __construct(
        MaritalStatusService $maritalStatusService,
        LoggerInterface          $logger,
        ResponseFactory          $responseFactory
    ) {
        $this->maritalStatusService = $maritalStatusService;
        $this->logger = $logger;
        $this->responseFactory = $responseFactory;
    }

    // Получение списка всех Должностей.
    #[Route('/', name: 'get_all', methods: ['GET'])]
    public function getMaritalStatus(): JsonResponse
    {
        try {
            $this->logger->info("Executing getMaritalStatus method.");
            $maritalStatusData = $this->maritalStatusService->getAllMaritalStatus();
            return $this->responseFactory->createSuccessResponse($maritalStatusData);
        } catch (\Exception $e) {
            $this->logger->error("Failed to fetch MaritalStatus: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to fetch MaritalStatus', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Получение данных Должности по её ID.
    #[Route('/{maritalStatusId}', name: 'get_id', methods: ['GET'])]
    public function getMaritalStatusId(int $maritalStatusId): JsonResponse
    {
        try {
            $this->logger->info("Executing getMaritalStatus method.");
            $maritalStatusData = $this->maritalStatusService->getMaritalStatusById($maritalStatusId);
            return $this->responseFactory->createSuccessResponse($maritalStatusData);
        } catch (\InvalidArgumentException $e) {
            $this->logger->warning($e->getMessage());
            return $this->responseFactory->createNotFoundResponse($e->getMessage());
        } catch (\Exception $e) {
            $this->logger->error("Failed to fetch MaritalStatus with ID $maritalStatusId: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to fetch MaritalStatus', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Создание новой должности (её кода).
    #[Route('/add', name: 'add', methods: ['POST'])]
    public function addMaritalStatus(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $responseData = $this->maritalStatusService->createMaritalStatus($data);
            return $this->responseFactory->createCreatedResponse($responseData);
        } catch (\InvalidArgumentException $e) {
            $this->logger->error("InvalidArgumentException caught in controller: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse($e->getMessage(), JsonResponse::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            $this->logger->error("Failed to add MaritalStatus: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to add MaritalStatus', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Добавление перевода для должности.
    #[Route('/{maritalStatusId}/add-translation', name: 'add_translation', methods: ['POST'])]
    public function addMaritalStatusTranslation(int $maritalStatusId, Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $responseData = $this->maritalStatusService->createMaritalStatusTranslation($maritalStatusId, $data);
            return $this->responseFactory->createCreatedResponse($responseData);
        } catch (\InvalidArgumentException $e) {
            return $this->responseFactory->createErrorResponse($e->getMessage(), JsonResponse::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            $this->logger->error("Failed to add translation for MaritalStatus ID $maritalStatusId: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to add MaritalStatus translation', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Обновление только основной должности (кода)
    #[Route('/{maritalStatusId}/update', name: 'update_id', methods: ['PUT'])]
    public function updateMaritalStatusCode(int $maritalStatusId, Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $responseData = $this->maritalStatusService->updateMaritalStatusCode($maritalStatusId, $data);
            return $this->responseFactory->createSuccessResponse($responseData);
        } catch (\InvalidArgumentException $e) {
            return $this->responseFactory->createErrorResponse($e->getMessage(), JsonResponse::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            $this->logger->error("Failed to update MaritalStatus code for ID $maritalStatusId: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to update MaritalStatus code', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Обновление перевода должности для указанного языка
    #[Route('/{maritalStatusId}/update-translation/{translationId}', name: 'update_translation', methods: ['PUT'])]
    public function updateMaritalStatusTranslation(int $maritalStatusId, int $translationId, Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $responseData = $this->maritalStatusService->updateMaritalStatusTranslation($maritalStatusId, $translationId, $data);
            return $this->responseFactory->createSuccessResponse($responseData);
        } catch (\InvalidArgumentException $e) {
            return $this->responseFactory->createErrorResponse($e->getMessage(), JsonResponse::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            $this->logger->error("Failed to update translation for MaritalStatus ID $maritalStatusId and language ID $translationId: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to update MaritalStatus translation', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Удаление конкретного перевода должности по его ID.
    #[Route('/{maritalStatusId}/delete-translation/{translationId}', name: 'delete_translation', methods: ['DELETE'])]
    public function deleteMaritalStatusTranslation(int $maritalStatusId, int $translationId): JsonResponse
    {
        try {
            $result = $this->maritalStatusService->deleteMaritalStatusTranslation($maritalStatusId, $translationId);
            return $this->responseFactory->createSuccessResponse($result);
        } catch (\InvalidArgumentException $e) {
            return $this->responseFactory->createNotFoundResponse($e->getMessage());
        } catch (\Exception $e) {
            $this->logger->error("Failed to delete translation with ID $translationId for MaritalStatus ID $maritalStatusId: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to delete MaritalStatus translation', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Удаление должности по ее ID.
    #[Route('/{maritalStatusId}/delete/', name: 'delete_id', methods: ['DELETE'])]
    public function deleteMaritalStatus(int $maritalStatusId): JsonResponse
    {
        try {
            $result = $this->maritalStatusService->deleteMaritalStatus($maritalStatusId);
            return $this->responseFactory->createSuccessResponse($result);
        } catch (\InvalidArgumentException $e) {
            return $this->responseFactory->createNotFoundResponse($e->getMessage());
        } catch (\Exception $e) {
            $this->logger->error("Failed to delete MaritalStatus with ID $maritalStatusId: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to delete MaritalStatus', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    //Для демо данных
    #[Route('/seed', name: 'seed', methods: ['POST'])]
    public function seedMaritalStatusAndTranslations(): JsonResponse
    {
        try {
            $result = $this->maritalStatusService->seedMaritalStatusAndTranslations();
            return $this->responseFactory->createCreatedResponse($result);
        } catch (\Exception $e) {
            $this->logger->error("Failed to seed MaritalStatus titles and translations: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to seed MaritalStatus titles and translations', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


}
