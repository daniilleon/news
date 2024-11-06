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
     * @param string $defaultMessage
     * @return JsonResponse
     */

    public function createSuccessResponse(array $data, string $defaultMessage = 'Operation successful.'): JsonResponse
    {
        // Если сообщение присутствует внутри данных, используем его, иначе используем стандартное
        $message = $data['message'] ?? $defaultMessage;

        // Удаляем `message` из данных, чтобы избежать дублирования
        unset($data['message']);

        $response = [
            'status' => 'success',
            'message' => $message,
            'data' => $data
        ];
        return new JsonResponse($response, JsonResponse::HTTP_OK);
    }

    /**
     * Создание ответа для успешного создания сущности.
     *
     * @param array $data
     * @param string $defaultMessage
     * @return JsonResponse
     */
    public function createCreatedResponse(array $data, string $defaultMessage = 'Operation successful.'): JsonResponse
    {
        // Если сообщение присутствует внутри данных, используем его, иначе используем стандартное
        $message = $data['message'] ?? $defaultMessage;

        // Удаляем `message` из данных, чтобы избежать дублирования
        unset($data['message']);

        $response = [
            'status' => 'success',
            'message' => $message,
            'data' => $data
        ];
        return new JsonResponse($response, JsonResponse::HTTP_CREATED);
    }

    /**
     * Создание ответа с сообщением об ошибке.
     *
     * @param string $message
     * @param int $statusCode
     * @param array $data Опциональные данные о ресурсе
     * @return JsonResponse
     */
    public function createErrorResponse(string $message,
                                        int $statusCode = JsonResponse::HTTP_BAD_REQUEST,
                                        array $data = []): JsonResponse
    {
        $this->logger->error($message);

        $response = [
            'status' => 'error',
            'message' => $message,
        ];


        if (!empty($data)) {
            $response['data'] = $data;
        }

        return new JsonResponse($response, $statusCode);
    }

    /**
     * Создание ответа для случая, когда ресурс не найден.
     *
     * @param string $message
     * @param array $data Опциональные данные о ресурсе
     * @return JsonResponse
     */
    public function createNotFoundResponse(string $message, array $data = []): JsonResponse
    {
        $this->logger->info($message);

        $response = [
            'status' => 'error',
            'message' => $message,
        ];

        if (!empty($data)) {
            $response['data'] = $data;
        }

        return new JsonResponse($response, JsonResponse::HTTP_NOT_FOUND);
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

}
