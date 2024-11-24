<?php

namespace Module\{{ENTITY_DIR}}\{{ENTITY_NAME}}\Controller\Api;

use Module\{{ENTITY_DIR}}\{{ENTITY_NAME}}\Service\{{ENTITY_NAME}}Service;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Module\Common\Factory\ResponseFactory;

// Основной контроллер API для работы с категориями.
#[Route('/api/{{ENTITY_DIR_LOWER_ONLY}}/{{ENTITY_NAME_LOWER_ONLY}}', name: 'api_{{ENTITY_NAME_LOWER_ONLY}}_')]
class {{ENTITY_NAME}}Controller
{
    private {{ENTITY_NAME}}Service ${{ENTITY_NAME_LOWER}}Service;
    private LoggerInterface $logger;
    private ResponseFactory $responseFactory;

    public function __construct(
        {{ENTITY_NAME}}Service ${{ENTITY_NAME_LOWER}}Service,
        LoggerInterface $logger,
        ResponseFactory $responseFactory
    ) {
        $this->{{ENTITY_NAME_LOWER}}Service = ${{ENTITY_NAME_LOWER}}Service;
        $this->logger = $logger;
        $this->responseFactory = $responseFactory;
    }

    // Получение списка всех категорий.
    #[Route('/', name: 'get_all', methods: ['GET'])]
    public function getAll{{ENTITY_NAME}}(): JsonResponse
    {
        try {
            $this->logger->info("Executing get{{ENTITY_NAME}} method.");
            ${{ENTITY_NAME_LOWER}}Data = $this->{{ENTITY_NAME_LOWER}}Service->getAll{{ENTITY_NAME_LOWER}}();
            return $this->responseFactory->createSuccessResponse(${{ENTITY_NAME_LOWER}}Data);
        } catch (\Exception $e) {
            $this->logger->error("Failed to fetch {{ENTITY_NAME_ONE}}: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to fetch {{ENTITY_NAME_ONE}}', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Получение данных категории по ее ID.
    #[Route('/{{{ENTITY_NAME_LOWER}}Id}', name: 'get_id', methods: ['GET'])]
    public function getId{{ENTITY_NAME_ONE}}(int ${{ENTITY_NAME_LOWER}}Id): JsonResponse
    {
        try {
            $this->logger->info("Executing get{{ENTITY_NAME_LOWER}} method.");
            ${{ENTITY_NAME_LOWER}}Data = $this->{{ENTITY_NAME_LOWER}}Service->get{{ENTITY_NAME_ONE}}ById(${{ENTITY_NAME_LOWER}}Id);
            return $this->responseFactory->createSuccessResponse(${{ENTITY_NAME_LOWER}}Data);
        } catch (\InvalidArgumentException $e) {
            $this->logger->warning($e->getMessage());
            return $this->responseFactory->createNotFoundResponse($e->getMessage());
        } catch (\Exception $e) {
            $this->logger->error("Failed to fetch {{ENTITY_NAME_ONE}} with ID ${{ENTITY_NAME_LOWER}}Id: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to fetch {{ENTITY_NAME_ONE}}', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Создание новой категории.
    #[Route('/add', name: 'add', methods: ['POST'])]
    public function add{{ENTITY_NAME_ONE}}(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $responseData = $this->{{ENTITY_NAME_LOWER}}Service->create{{ENTITY_NAME_ONE}}($data);
            return $this->responseFactory->createCreatedResponse($responseData);
        } catch (\InvalidArgumentException $e) {
            $this->logger->error("InvalidArgumentException caught in controller: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse($e->getMessage(), JsonResponse::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            $this->logger->error("Failed to add {{ENTITY_NAME_ONE}}: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to add {{ENTITY_NAME_ONE}}', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

//    #[Route('/{{{ENTITY_NAME_LOWER}}Id}/upload-image', name: 'upload_image', methods: ['POST'])]
//    public function update{{ENTITY_NAME_LOWER}}Image(int ${{ENTITY_NAME_LOWER}}Id, Request $request): JsonResponse
//    {
//        try {
//            // Получаем файл og_image из запроса и передаем его в сервис без проверки
//            $file = $request->files->get('og_image');
//            // Вызываем метод сервиса для обновления изображения
//            $responseData = $this->industriesService->update{{ENTITY_NAME_LOWER}}Image(${{ENTITY_NAME_LOWER}}Id, $file);
//            return $this->responseFactory->createSuccessResponse($responseData);
//        } catch (\InvalidArgumentException $e) {
//            $this->logger->error("InvalidArgumentException caught in controller: " . $e->getMessage());
//            return $this->responseFactory->createErrorResponse($e->getMessage(), JsonResponse::HTTP_BAD_REQUEST);
//        } catch (\Exception $e) {
//            $this->logger->error("Failed to update {{ENTITY_NAME_LOWER}} image: " . $e->getMessage());
//            return $this->responseFactory->createErrorResponse('Unable to update {{ENTITY_NAME_LOWER}} image', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
//        }
//    }


    // Добавление перевода для категории.
    #[Route('/{{{ENTITY_NAME_LOWER}}Id}/add-translation', name: 'add_translation', methods: ['POST'])]
    public function add{{ENTITY_NAME_ONE}}Translation(int ${{ENTITY_NAME_LOWER}}Id, Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $responseData = $this->{{ENTITY_NAME_LOWER}}Service->create{{ENTITY_NAME_ONE}}Translation(${{ENTITY_NAME_LOWER}}Id, $data);
            return $this->responseFactory->createCreatedResponse($responseData);
        } catch (\InvalidArgumentException $e) {
            return $this->responseFactory->createErrorResponse($e->getMessage(), JsonResponse::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            $this->logger->error("Failed to add translation for {{ENTITY_NAME_LOWER}} ID ${{ENTITY_NAME_LOWER}}Id: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to add {{ENTITY_NAME_LOWER}} translation', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Обновление только основной категории (ссылки)
    #[Route('/{{{ENTITY_NAME_LOWER}}Id}/update', name: 'update_id', methods: ['PUT'])]
    public function update{{ENTITY_NAME_ONE}}{{ENTITY_CODE_LINK}}(int ${{ENTITY_NAME_LOWER}}Id, Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $responseData = $this->{{ENTITY_NAME_LOWER}}Service->update{{ENTITY_NAME_ONE}}{{ENTITY_CODE_LINK}}(${{ENTITY_NAME_LOWER}}Id, $data);
            return $this->responseFactory->createSuccessResponse($responseData);
        } catch (\InvalidArgumentException $e) {
            return $this->responseFactory->createErrorResponse($e->getMessage(), JsonResponse::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            $this->logger->error("Failed to update {{ENTITY_NAME_ONE}} {{ENTITY_CODE_LINK}} for ID ${{ENTITY_NAME_LOWER}}Id: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to update {{ENTITY_NAME_ONE}} {{ENTITY_CODE_LINK}}', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Обновление перевода категории для указанного языка
    #[Route('/{{{ENTITY_NAME_LOWER}}Id}/update-translation/{translationId}', name: 'update_translation', methods: ['PUT'])]
    public function update{{ENTITY_NAME_ONE}}Translation(int ${{ENTITY_NAME_LOWER}}Id, int $translationId, Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $responseData = $this->{{ENTITY_NAME_LOWER}}Service->update{{ENTITY_NAME_ONE}}Translation(${{ENTITY_NAME_LOWER}}Id, $translationId, $data);
            return $this->responseFactory->createSuccessResponse($responseData);
        } catch (\InvalidArgumentException $e) {
            return $this->responseFactory->createErrorResponse($e->getMessage(), JsonResponse::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            $this->logger->error("Failed to update translation for {{ENTITY_NAME_ONE}} ID ${{ENTITY_NAME_LOWER}}Id and language ID $translationId: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to update {{ENTITY_NAME_ONE}} translation', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Удаление конкретного перевода категории по его ID.
    #[Route('/{{{ENTITY_NAME_LOWER}}Id}/delete-translation/{translationId}', name: 'delete_translation', methods: ['DELETE'])]
    public function delete{{ENTITY_NAME_ONE}}Translation(int ${{ENTITY_NAME_LOWER}}Id, int $translationId): JsonResponse
    {
        try {
            $result = $this->{{ENTITY_NAME_LOWER}}Service->delete{{ENTITY_NAME_ONE}}Translation(${{ENTITY_NAME_LOWER}}Id, $translationId);
            return $this->responseFactory->createSuccessResponse($result);
        } catch (\InvalidArgumentException $e) {
            return $this->responseFactory->createNotFoundResponse($e->getMessage());
        } catch (\Exception $e) {
            $this->logger->error("Failed to delete translation with ID $translationId for {{ENTITY_NAME_ONE}} ID ${{ENTITY_NAME_LOWER}}Id: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to delete {{ENTITY_NAME_ONE}} translation', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Удаление категории по ее ID.
    #[Route('/{id}/delete', name: 'delete_id', methods: ['DELETE'])]
    public function delete{{ENTITY_NAME_ONE}}(int $id): JsonResponse
    {
        try {
            $result = $this->{{ENTITY_NAME_LOWER}}Service->delete{{ENTITY_NAME_ONE}}($id);
            return $this->responseFactory->createSuccessResponse($result);
        } catch (\InvalidArgumentException $e) {
            return $this->responseFactory->createNotFoundResponse($e->getMessage());
        } catch (\Exception $e) {
            $this->logger->error("Failed to delete {{ENTITY_NAME_ONE}} with ID $id: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to delete {{ENTITY_NAME_ONE}}', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/seed', name: 'seed', methods: ['POST'])]
    public function seed{{ENTITY_NAME_ONE}}AndTranslations(): JsonResponse
    {
        try {
            $result = $this->{{ENTITY_NAME_LOWER}}Service->seed{{ENTITY_NAME_ONE}}AndTranslations();
            return $this->responseFactory->createCreatedResponse($result);
        } catch (\Exception $e) {
            $this->logger->error("Failed to seed {{ENTITY_NAME_ONE}} and translations: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to seed {{ENTITY_NAME_ONE}} and translations', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}
