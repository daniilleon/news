<?php

namespace Module\Shared\Industries\Controller\Api;

use Module\Shared\Industries\Service\IndustriesService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Module\Common\Factory\ResponseFactory;

// Основной контроллер API для работы с категориями.
#[Route('/api/shared/industries', name: 'api_industries_')]
class IndustriesController
{
    private IndustriesService $industriesService;
    private LoggerInterface $logger;
    private ResponseFactory $responseFactory;

    public function __construct(
        IndustriesService $industriesService,
        LoggerInterface $logger,
        ResponseFactory $responseFactory
    ) {
        $this->industriesService = $industriesService;
        $this->logger = $logger;
        $this->responseFactory = $responseFactory;
    }

    // Получение списка всех категорий.
    #[Route('/', name: 'get_all', methods: ['GET'])]
    public function getIndustries(): JsonResponse
    {
        try {
            $this->logger->info("Executing getIndustries method.");
            $industriesData = $this->industriesService->getAllIndustries();
            return $this->responseFactory->createSuccessResponse($industriesData);
        } catch (\Exception $e) {
            $this->logger->error("Failed to fetch industries: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to fetch industries', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Получение данных категории по ее ID.
    #[Route('/{industryId}', name: 'get_id', methods: ['GET'])]
    public function getIndustry(int $industryId): JsonResponse
    {
        try {
            $this->logger->info("Executing getIndustry method.");
            $industryData = $this->industriesService->getIndustryById($industryId);
            return $this->responseFactory->createSuccessResponse($industryData);
        } catch (\InvalidArgumentException $e) {
            $this->logger->warning($e->getMessage());
            return $this->responseFactory->createNotFoundResponse($e->getMessage());
        } catch (\Exception $e) {
            $this->logger->error("Failed to fetch industry with ID $industryId: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to fetch industry', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Создание новой категории.
    #[Route('/add', name: 'add', methods: ['POST'])]
    public function addIndustry(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $responseData = $this->industriesService->createIndustry($data);
            return $this->responseFactory->createCreatedResponse($responseData);
        } catch (\InvalidArgumentException $e) {
            $this->logger->error("InvalidArgumentException caught in controller: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse($e->getMessage(), JsonResponse::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            $this->logger->error("Failed to add industry: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to add industry', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{industryId}/upload-image', name: 'upload_image', methods: ['POST'])]
    public function updateIndustryImage(int $industryId, Request $request): JsonResponse
    {
        try {
            // Получаем файл og_image из запроса и передаем его в сервис без проверки
            $file = $request->files->get('og_image');
            // Вызываем метод сервиса для обновления изображения
            $responseData = $this->industriesService->updateIndustryImage($industryId, $file);
            return $this->responseFactory->createSuccessResponse($responseData);
        } catch (\InvalidArgumentException $e) {
            $this->logger->error("InvalidArgumentException caught in controller: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse($e->getMessage(), JsonResponse::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            $this->logger->error("Failed to update industry image: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to update industry image', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    // Добавление перевода для категории.
    #[Route('/{industryId}/add-translation', name: 'add_translation', methods: ['POST'])]
    public function addIndustryTranslation(int $industryId, Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $responseData = $this->industriesService->createIndustryTranslation($industryId, $data);
            return $this->responseFactory->createCreatedResponse($responseData);
        } catch (\InvalidArgumentException $e) {
            return $this->responseFactory->createErrorResponse($e->getMessage(), JsonResponse::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            $this->logger->error("Failed to add translation for industry ID $industryId: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to add industry translation', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Обновление только основной категории (ссылки)
    #[Route('/{industryId}/update', name: 'update_id', methods: ['PUT'])]
    public function updateIndustryLink(int $industryId, Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $responseData = $this->industriesService->updateIndustryLink($industryId, $data);
            return $this->responseFactory->createSuccessResponse($responseData);
        } catch (\InvalidArgumentException $e) {
            return $this->responseFactory->createErrorResponse($e->getMessage(), JsonResponse::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            $this->logger->error("Failed to update industry link for ID $industryId: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to update industry link', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Обновление перевода категории для указанного языка
    #[Route('/{industryId}/update-translation/{translationId}', name: 'update_translation', methods: ['PUT'])]
    public function updateIndustryTranslation(int $industryId, int $translationId, Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $responseData = $this->industriesService->updateIndustryTranslation($industryId, $translationId, $data);
            return $this->responseFactory->createSuccessResponse($responseData);
        } catch (\InvalidArgumentException $e) {
            return $this->responseFactory->createErrorResponse($e->getMessage(), JsonResponse::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            $this->logger->error("Failed to update translation for industry ID $industryId and language ID $translationId: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to update industry translation', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Удаление конкретного перевода категории по его ID.
    #[Route('/{industryId}/delete-translation/{translationId}', name: 'delete_translation', methods: ['DELETE'])]
    public function deleteIndustryTranslation(int $industryId, int $translationId): JsonResponse
    {
        try {
            $result = $this->industriesService->deleteIndustryTranslation($industryId, $translationId);
            return $this->responseFactory->createSuccessResponse($result);
        } catch (\InvalidArgumentException $e) {
            return $this->responseFactory->createNotFoundResponse($e->getMessage());
        } catch (\Exception $e) {
            $this->logger->error("Failed to delete translation with ID $translationId for industry ID $industryId: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to delete industry translation', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Удаление категории по ее ID.
    #[Route('/{id}/delete', name: 'delete_id', methods: ['DELETE'])]
    public function deleteIndustry(int $id): JsonResponse
    {
        try {
            $result = $this->industriesService->deleteIndustry($id);
            return $this->responseFactory->createSuccessResponse($result);
        } catch (\InvalidArgumentException $e) {
            return $this->responseFactory->createNotFoundResponse($e->getMessage());
        } catch (\Exception $e) {
            $this->logger->error("Failed to delete industry with ID $id: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to delete industry', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/seed', name: 'seed', methods: ['POST'])]
    public function seedIndustriesAndTranslations(): JsonResponse
    {
        try {
            $result = $this->industriesService->seedIndustriesAndTranslations();
            return $this->responseFactory->createCreatedResponse($result);
        } catch (\Exception $e) {
            $this->logger->error("Failed to seed industries and translations: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to seed industries and translations', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}
