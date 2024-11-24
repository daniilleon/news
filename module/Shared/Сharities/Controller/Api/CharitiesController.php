<?php

namespace Module\Shared\Charities\Controller\Api;

use Module\Shared\Charities\Service\CharitiesService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Module\Common\Factory\ResponseFactory;

// Основной контроллер API для работы с категориями.
#[Route('/api/shared/charities', name: 'api_charities_')]
class CharitiesController
{
    private CharitiesService $charitiesService;
    private LoggerInterface $logger;
    private ResponseFactory $responseFactory;

    public function __construct(
        CharitiesService $charitiesService,
        LoggerInterface $logger,
        ResponseFactory $responseFactory
    ) {
        $this->charitiesService = $charitiesService;
        $this->logger = $logger;
        $this->responseFactory = $responseFactory;
    }

    // Получение списка всех категорий.
    #[Route('/', name: 'get_all', methods: ['GET'])]
    public function getCharities(): JsonResponse
    {
        try {
            $this->logger->info("Executing getCharities method.");
            $charitiesData = $this->charitiesService->getAllCharities();
            return $this->responseFactory->createSuccessResponse($charitiesData);
        } catch (\Exception $e) {
            $this->logger->error("Failed to fetch charities: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to fetch charities', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Получение данных категории по ее ID.
    #[Route('/{charityId}', name: 'get_id', methods: ['GET'])]
    public function getCharity(int $charityId): JsonResponse
    {
        try {
            $this->logger->info("Executing getCharity method.");
            $charityData = $this->charitiesService->getCharityById($charityId);
            return $this->responseFactory->createSuccessResponse($charityData);
        } catch (\InvalidArgumentException $e) {
            $this->logger->warning($e->getMessage());
            return $this->responseFactory->createNotFoundResponse($e->getMessage());
        } catch (\Exception $e) {
            $this->logger->error("Failed to fetch charity with ID $charityId: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to fetch charity', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Создание новой категории.
    #[Route('/add', name: 'add', methods: ['POST'])]
    public function addCharity(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $responseData = $this->charitiesService->createCharity($data);
            return $this->responseFactory->createCreatedResponse($responseData);
        } catch (\InvalidArgumentException $e) {
            $this->logger->error("InvalidArgumentException caught in controller: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse($e->getMessage(), JsonResponse::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            $this->logger->error("Failed to add charity: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to add charity', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{charityId}/upload-image', name: 'upload_image', methods: ['POST'])]
    public function updateCharityImage(int $charityId, Request $request): JsonResponse
    {
        try {
            // Получаем файл og_image из запроса и передаем его в сервис без проверки
            $file = $request->files->get('og_image');
            // Вызываем метод сервиса для обновления изображения
            $responseData = $this->charitiesService->updateCharityImage($charityId, $file);
            return $this->responseFactory->createSuccessResponse($responseData);
        } catch (\InvalidArgumentException $e) {
            $this->logger->error("InvalidArgumentException caught in controller: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse($e->getMessage(), JsonResponse::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            $this->logger->error("Failed to update charity image: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to update charity image', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    // Добавление перевода для категории.
    #[Route('/{charityId}/add-translation', name: 'add_translation', methods: ['POST'])]
    public function addCharityTranslation(int $charityId, Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $responseData = $this->charitiesService->createCharityTranslation($charityId, $data);
            return $this->responseFactory->createCreatedResponse($responseData);
        } catch (\InvalidArgumentException $e) {
            return $this->responseFactory->createErrorResponse($e->getMessage(), JsonResponse::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            $this->logger->error("Failed to add translation for charity ID $charityId: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to add charity translation', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Обновление только основной категории (ссылки)
    #[Route('/{charityId}/update', name: 'update_id', methods: ['PUT'])]
    public function updateCharityLink(int $charityId, Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $responseData = $this->charitiesService->updateCharityLink($charityId, $data);
            return $this->responseFactory->createSuccessResponse($responseData);
        } catch (\InvalidArgumentException $e) {
            return $this->responseFactory->createErrorResponse($e->getMessage(), JsonResponse::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            $this->logger->error("Failed to update charity link for ID $charityId: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to update charity link', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Обновление перевода категории для указанного языка
    #[Route('/{charityId}/update-translation/{translationId}', name: 'update_translation', methods: ['PUT'])]
    public function updateCharityTranslation(int $charityId, int $translationId, Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $responseData = $this->charitiesService->updateCharityTranslation($charityId, $translationId, $data);
            return $this->responseFactory->createSuccessResponse($responseData);
        } catch (\InvalidArgumentException $e) {
            return $this->responseFactory->createErrorResponse($e->getMessage(), JsonResponse::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            $this->logger->error("Failed to update translation for charity ID $charityId and language ID $translationId: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to update charity translation', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Удаление конкретного перевода категории по его ID.
    #[Route('/{charityId}/delete-translation/{translationId}', name: 'delete_translation', methods: ['DELETE'])]
    public function deleteCharityTranslation(int $charityId, int $translationId): JsonResponse
    {
        try {
            $result = $this->charitiesService->deleteCharityTranslation($charityId, $translationId);
            return $this->responseFactory->createSuccessResponse($result);
        } catch (\InvalidArgumentException $e) {
            return $this->responseFactory->createNotFoundResponse($e->getMessage());
        } catch (\Exception $e) {
            $this->logger->error("Failed to delete translation with ID $translationId for charity ID $charityId: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to delete charity translation', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Удаление категории по ее ID.
    #[Route('/{id}/delete', name: 'delete_id', methods: ['DELETE'])]
    public function deleteCharity(int $id): JsonResponse
    {
        try {
            $result = $this->charitiesService->deleteCharity($id);
            return $this->responseFactory->createSuccessResponse($result);
        } catch (\InvalidArgumentException $e) {
            return $this->responseFactory->createNotFoundResponse($e->getMessage());
        } catch (\Exception $e) {
            $this->logger->error("Failed to delete charity with ID $id: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to delete charity', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/seed', name: 'seed', methods: ['POST'])]
    public function seedCharitiesAndTranslations(): JsonResponse
    {
        try {
            $result = $this->charitiesService->seedCharitiesAndTranslations();
            return $this->responseFactory->createCreatedResponse($result);
        } catch (\Exception $e) {
            $this->logger->error("Failed to seed charities and translations: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to seed charities and translations', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}
