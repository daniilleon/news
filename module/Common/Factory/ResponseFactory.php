<?php

namespace Module\Common\Factory;

use Symfony\Component\HttpFoundation\JsonResponse;
use Psr\Log\LoggerInterface;

class ResponseFactory
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Создание успешного ответа с данными.
     *
     * @param array $data
     * @param int $statusCode
     * @return JsonResponse
     */
    public function createSuccessResponse(array $data, int $statusCode = JsonResponse::HTTP_OK): JsonResponse
    {
        return new JsonResponse($data, $statusCode);
    }

    /**
     * Создание ответа с сообщением об ошибке.
     *
     * @param string $message
     * @param int $statusCode
     * @return JsonResponse
     */
    public function createErrorResponse(string $message, int $statusCode = JsonResponse::HTTP_BAD_REQUEST): JsonResponse
    {
        $this->logger->error($message);
        return new JsonResponse(['error' => $message], $statusCode);
    }

    /**
     * Создание ответа с валидационной ошибкой.
     *
     * @param array $validationErrors
     * @return JsonResponse
     */
    public function createValidationErrorResponse(array $validationErrors): JsonResponse
    {
        $this->logger->error('Validation errors occurred', $validationErrors);
        return new JsonResponse(['validation_errors' => $validationErrors], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
    }

    /**
     * Создание ответа для успешного создания сущности.
     *
     * @param array $data
     * @return JsonResponse
     */
    public function createCreatedResponse(array $data, int $statusCode = JsonResponse::HTTP_CREATED): JsonResponse
    {
        return new JsonResponse($data, $statusCode);
    }

    /**
     * Создание ответа для случая, когда ресурс не найден.
     *
     * @param string $message
     * @return JsonResponse
     */
    public function createNotFoundResponse(string $message): JsonResponse
    {
        $this->logger->info($message);
        return new JsonResponse(['error' => $message], JsonResponse::HTTP_NOT_FOUND);
    }
}
