<?php

namespace Module\Employees\EmployeesJobTitle\Controller\Api;

use Module\Employees\EmployeesJobTitle\Service\EmployeesJobTitleService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Module\Common\Factory\ResponseFactory;

//Контроллер API для работы с должностями.
#[Route('/api/employees/job_title')]
class EmployeesJobTitleController
{
    private EmployeesJobTitleService $employeesJobTitleService;
    private LoggerInterface $logger;
    private ResponseFactory $responseFactory;

    public function __construct(
        EmployeesJobTitleService $employeesJobTitleService,
        LoggerInterface          $logger,
        ResponseFactory          $responseFactory
    ) {
        $this->employeesJobTitleService = $employeesJobTitleService;
        $this->logger = $logger;
        $this->responseFactory = $responseFactory;
    }

    // Получение списка всех Должностей.
    #[Route('/', name: 'api_get_employees_job_title', methods: ['GET'])]
    public function getEmployeesJobTitle(): JsonResponse
    {
        try {
            $this->logger->info("Executing getEmployeesJobTitle method.");
            $employeesJobTitleData = $this->employeesJobTitleService->getAllEmployeesJobTitle();
            return $this->responseFactory->createSuccessResponse($employeesJobTitleData);
        } catch (\Exception $e) {
            $this->logger->error("Failed to fetch EmployeesJobTitle: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to fetch EmployeesJobTitle', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Получение данных Должности по её ID.
    #[Route('/{employeeJobTitleId}', name: 'api_get_employee_job_title', methods: ['GET'])]
    public function getEmployeeJobTitle(int $employeeJobTitleId): JsonResponse
    {
        try {
            $this->logger->info("Executing getEmployeeJobTitle method.");
            $employeesJobTitleData = $this->employeesJobTitleService->getEmployeeJobTitleById($employeeJobTitleId);
            return $this->responseFactory->createSuccessResponse($employeesJobTitleData);
        } catch (\InvalidArgumentException $e) {
            $this->logger->warning($e->getMessage());
            return $this->responseFactory->createNotFoundResponse($e->getMessage());
        } catch (\Exception $e) {
            $this->logger->error("Failed to fetch EmployeeJobTitle with ID $employeeJobTitleId: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to fetch EmployeeJobTitle', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Создание новой должности (её кода).
    #[Route('/add', name: 'api_add_employee_job_title_add', methods: ['POST'])]
    public function addEmployeeJobTitle(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $responseData = $this->employeesJobTitleService->createEmployeeJobTitle($data);
            return $this->responseFactory->createCreatedResponse($responseData);
        } catch (\InvalidArgumentException $e) {
            $this->logger->error("InvalidArgumentException caught in controller: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse($e->getMessage(), JsonResponse::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            $this->logger->error("Failed to add EmployeesJobTitle: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to add EmployeeJobTitle', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Добавление перевода для должности.
    #[Route('/{employeeJobTitleId}/add-translation', name: 'api_add_employee_job_title_translation', methods: ['POST'])]
    public function addEmployeeJobTitleTranslation(int $employeeJobTitleId, Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $responseData = $this->employeesJobTitleService->createEmployeeJobTitleTranslation($employeeJobTitleId, $data);
            return $this->responseFactory->createCreatedResponse($responseData);
        } catch (\InvalidArgumentException $e) {
            return $this->responseFactory->createErrorResponse($e->getMessage(), JsonResponse::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            $this->logger->error("Failed to add translation for EmployeeJobTitle ID $employeeJobTitleId: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to add EmployeeJobTitle translation', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Обновление только основной должности (кода)
    #[Route('/{employeeJobTitleId}/update', name: 'api_update_employee_job_title', methods: ['PUT'])]
    public function updateEmployeeJobTitleCode(int $employeeJobTitleId, Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $responseData = $this->employeesJobTitleService->updateEmployeeJobTitleCode($employeeJobTitleId, $data);
            return $this->responseFactory->createSuccessResponse($responseData);
        } catch (\InvalidArgumentException $e) {
            return $this->responseFactory->createErrorResponse($e->getMessage(), JsonResponse::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            $this->logger->error("Failed to update EmployeeJobTitle code for ID $employeeJobTitleId: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to update EmployeeJobTitle code', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Обновление перевода должности для указанного языка
    #[Route('/{employeeJobTitleId}/update-translation/{translationId}', name: 'api_update_employee_job_title_translation', methods: ['PUT'])]
    public function updateEmployeeJobTitleTranslation(int $employeeJobTitleId, int $translationId, Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $responseData = $this->employeesJobTitleService->updateEmployeeJobTitleTranslation($employeeJobTitleId, $translationId, $data);
            return $this->responseFactory->createSuccessResponse($responseData);
        } catch (\InvalidArgumentException $e) {
            return $this->responseFactory->createErrorResponse($e->getMessage(), JsonResponse::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            $this->logger->error("Failed to update translation for EmployeeJobTitle ID $employeeJobTitleId and language ID $translationId: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to update EmployeeJobTitle translation', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Удаление конкретного перевода должности по его ID.
    #[Route('/{employeeJobTitleId}/delete-translation/{translationId}', name: 'api_delete_employee_job_title_translation', methods: ['DELETE'])]
    public function deleteEmployeeJobTitleTranslation(int $employeeJobTitleId, int $translationId): JsonResponse
    {
        try {
            $result = $this->employeesJobTitleService->deleteEmployeeJobTitleTranslation($employeeJobTitleId, $translationId);
            return $this->responseFactory->createSuccessResponse($result);
        } catch (\InvalidArgumentException $e) {
            return $this->responseFactory->createNotFoundResponse($e->getMessage());
        } catch (\Exception $e) {
            $this->logger->error("Failed to delete translation with ID $translationId for EmployeeJobTitle ID $employeeJobTitleId: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to delete EmployeeJobTitle translation', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Удаление должности по ее ID.
    #[Route('/{employeeJobTitleId}/delete/', name: 'api_delete_employee_job_title', methods: ['DELETE'])]
    public function deleteEmployeeJobTitle(int $employeeJobTitleId): JsonResponse
    {
        try {
            $result = $this->employeesJobTitleService->deleteEmployeeJobTitle($employeeJobTitleId);
            return $this->responseFactory->createSuccessResponse($result);
        } catch (\InvalidArgumentException $e) {
            return $this->responseFactory->createNotFoundResponse($e->getMessage());
        } catch (\Exception $e) {
            $this->logger->error("Failed to delete EmployeeJobTitle with ID $employeeJobTitleId: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to delete EmployeeJobTitle', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    //Для демо данных
    #[Route('/seed', name: 'api_seed_job_titles_and_translations', methods: ['POST'])]
    public function seedJobTitlesAndTranslations(): JsonResponse
    {
        try {
            $result = $this->employeesJobTitleService->seedJobTitlesAndTranslations();
            return $this->responseFactory->createCreatedResponse($result);
        } catch (\Exception $e) {
            $this->logger->error("Failed to seed job titles and translations: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to seed job titles and translations', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


}
