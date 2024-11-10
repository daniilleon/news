<?php

namespace Module\Categories\Controller\Api;

use Module\Categories\Service\CategoriesService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Module\Common\Factory\ResponseFactory;

// Основной контроллер API для работы с категориями.
#[Route('/api/categories')]
class CategoriesController
{
    private CategoriesService $categoriesService;
    private LoggerInterface $logger;
    private ResponseFactory $responseFactory;

    public function __construct(
        CategoriesService $categoriesService,
        LoggerInterface $logger,
        ResponseFactory $responseFactory
    ) {
        $this->categoriesService = $categoriesService;
        $this->logger = $logger;
        $this->responseFactory = $responseFactory;
    }

    // Получение списка всех категорий.
    #[Route('/', name: 'api_get_categories', methods: ['GET'])]
    public function getCategories(): JsonResponse
    {
        try {
            $this->logger->info("Executing getCategories method.");
            $categoriesData = $this->categoriesService->getAllCategories();
            return $this->responseFactory->createSuccessResponse($categoriesData);
        } catch (\Exception $e) {
            $this->logger->error("Failed to fetch categories: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to fetch categories', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Получение данных категории по ее ID.
    #[Route('/{categoryId}', name: 'api_get_category', methods: ['GET'])]
    public function getCategory(int $categoryId): JsonResponse
    {
        try {
            $this->logger->info("Executing getCategory method.");
            $categoryData = $this->categoriesService->getCategoryById($categoryId);
            return $this->responseFactory->createSuccessResponse($categoryData);
        } catch (\InvalidArgumentException $e) {
            $this->logger->warning($e->getMessage());
            return $this->responseFactory->createNotFoundResponse($e->getMessage());
        } catch (\Exception $e) {
            $this->logger->error("Failed to fetch category with ID $categoryId: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to fetch category', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Создание новой категории.
    #[Route('/add', name: 'api_add_category', methods: ['POST'])]
    public function addCategory(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $responseData = $this->categoriesService->createCategory($data);
            return $this->responseFactory->createCreatedResponse($responseData);
        } catch (\InvalidArgumentException $e) {
            $this->logger->error("InvalidArgumentException caught in controller: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse($e->getMessage(), JsonResponse::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            $this->logger->error("Failed to add category: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to add category', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{categoryId}/upload-image', name: 'api_update_category_image', methods: ['POST'])]
    public function updateCategoryImage(int $categoryId, Request $request): JsonResponse
    {
        try {
            // Получаем файл og_image из запроса и передаем его в сервис без проверки
            $file = $request->files->get('og_image');
            // Вызываем метод сервиса для обновления изображения
            $responseData = $this->categoriesService->updateCategoryImage($categoryId, $file);
            return $this->responseFactory->createSuccessResponse($responseData);
        } catch (\InvalidArgumentException $e) {
            $this->logger->error("InvalidArgumentException caught in controller: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse($e->getMessage(), JsonResponse::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            $this->logger->error("Failed to update category image: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to update category image', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    // Добавление перевода для категории.
    #[Route('/{categoryId}/add-translation', name: 'api_add_category_translation', methods: ['POST'])]
    public function addCategoryTranslation(int $categoryId, Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $responseData = $this->categoriesService->createCategoryTranslation($categoryId, $data);
            return $this->responseFactory->createCreatedResponse($responseData);
        } catch (\InvalidArgumentException $e) {
            return $this->responseFactory->createErrorResponse($e->getMessage(), JsonResponse::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            $this->logger->error("Failed to add translation for category ID $categoryId: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to add category translation', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Обновление только основной категории (ссылки)
    #[Route('/{categoryId}/update', name: 'api_update_category', methods: ['PUT'])]
    public function updateCategoryLink(int $categoryId, Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $responseData = $this->categoriesService->updateCategoryLink($categoryId, $data);
            return $this->responseFactory->createSuccessResponse($responseData);
        } catch (\InvalidArgumentException $e) {
            return $this->responseFactory->createErrorResponse($e->getMessage(), JsonResponse::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            $this->logger->error("Failed to update category link for ID $categoryId: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to update category link', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Обновление перевода категории для указанного языка
    #[Route('/{categoryId}/update-translation/{translationId}', name: 'api_update_category_translation', methods: ['PUT'])]
    public function updateCategoryTranslation(int $categoryId, int $translationId, Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $responseData = $this->categoriesService->updateCategoryTranslation($categoryId, $translationId, $data);
            return $this->responseFactory->createSuccessResponse($responseData);
        } catch (\InvalidArgumentException $e) {
            return $this->responseFactory->createErrorResponse($e->getMessage(), JsonResponse::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            $this->logger->error("Failed to update translation for category ID $categoryId and language ID $translationId: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to update category translation', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Удаление конкретного перевода категории по его ID.
    #[Route('/{categoryId}/delete-translation/{translationId}', name: 'api_delete_category_translation', methods: ['DELETE'])]
    public function deleteCategoryTranslation(int $categoryId, int $translationId): JsonResponse
    {
        try {
            $result = $this->categoriesService->deleteCategoryTranslation($categoryId, $translationId);
            return $this->responseFactory->createSuccessResponse($result);
        } catch (\InvalidArgumentException $e) {
            return $this->responseFactory->createNotFoundResponse($e->getMessage());
        } catch (\Exception $e) {
            $this->logger->error("Failed to delete translation with ID $translationId for category ID $categoryId: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to delete category translation', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Удаление категории по ее ID.
    #[Route('/{id}/delete/', name: 'api_delete_category', methods: ['DELETE'])]
    public function deleteCategory(int $id): JsonResponse
    {
        try {
            $result = $this->categoriesService->deleteCategory($id);
            return $this->responseFactory->createSuccessResponse($result);
        } catch (\InvalidArgumentException $e) {
            return $this->responseFactory->createNotFoundResponse($e->getMessage());
        } catch (\Exception $e) {
            $this->logger->error("Failed to delete category with ID $id: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to delete category', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/seed', name: 'api_seed_categories_and_translations', methods: ['POST'])]
    public function seedCategoriesAndTranslations(): JsonResponse
    {
        try {
            $result = $this->categoriesService->seedCategoriesAndTranslations();
            return $this->responseFactory->createCreatedResponse($result);
        } catch (\Exception $e) {
            $this->logger->error("Failed to seed categories and translations: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to seed categories and translations', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}
