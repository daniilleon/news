<?php
namespace Module\Employees\Employees\Controller\Api;

use Module\Common\Factory\ResponseFactory;
use Module\Employees\Employees\Service\EmployeesService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

// Основной контроллер API для работы с сотрудниками.
#[Route('/api/employees/staff', name: 'api_employees_staff_')]
class EmployeesController
{
    private EmployeesService $employeeService;
    private LoggerInterface $logger;
    private ResponseFactory $responseFactory;

    // Конструктор инициализирует сервис сотрудников и логгер.
    public function __construct(EmployeesService $employeeService, LoggerInterface $logger, ResponseFactory $responseFactory)
    {
        $this->employeeService = $employeeService;
        $this->logger = $logger;
        $this->responseFactory = $responseFactory;
        //$this->logger->info("EmployeeController instance created.");
    }

    // Получение списка всех сотрудников.
    #[Route('/', name: 'get_all', methods: ['GET'])]
    public function getEmployees(): JsonResponse
    {
        try {
            $this->logger->info("Executing getEmployees method.");
            $employeesData = $this->employeeService->getAllEmployees();
            return $this->responseFactory->createSuccessResponse($employeesData);
        } catch (\Exception $e) {
            $this->logger->error("Failed to fetch employees: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to fetch employees', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Получение данных сотрудника по его ID.
    #[Route('/{id}', name: 'get_id', methods: ['GET'])]
    public function getEmployee(int $id): JsonResponse
    {
        try {
            $this->logger->info("Executing getEmployee method for ID: $id");
            $employeeData = $this->employeeService->getEmployeeById($id);
            // Возвращаем успешный ответ с данными
            return $this->responseFactory->createSuccessResponse($employeeData);
        } catch (\InvalidArgumentException $e) {
            // Логирование и ответ при отсутствии сотрудника
            $this->logger->warning($e->getMessage());
            return $this->responseFactory->createNotFoundResponse($e->getMessage());
        } catch (\Exception $e) {
            // Логирование общей ошибки и возврат внутренней ошибки
            $this->logger->error("Failed to fetch employee with ID $id: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to fetch employee', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Добавление нового сотрудника
    #[Route('/add', name: 'add', methods: ['POST'])]
    public function addEmployee(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $responseData = $this->employeeService->createEmployee($data);
            return $this->responseFactory->createCreatedResponse($responseData);
        } catch (\InvalidArgumentException $e) {
            $this->logger->error("InvalidArgumentException caught in controller: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse($e->getMessage(), JsonResponse::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            $this->logger->error("Failed to add employee: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to add employee', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Обновление данных сотрудника
    #[Route('/{id}/update', name: 'update_id', methods: ['PUT'])]
    public function updateEmployee(int $id, Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $responseData = $this->employeeService->updateEmployee($id, $data);
            return $this->responseFactory->createSuccessResponse($responseData);
        } catch (\InvalidArgumentException $e) {
            return $this->responseFactory->createErrorResponse($e->getMessage(), JsonResponse::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            $this->logger->error("Failed to update employee with ID $id: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to update employee', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Активация или деактивация сотрудника
    #[Route('/{id}/toggle-status', name: 'toggle_status', methods: ['PUT'])]
    public function toggleEmployeeStatus(int $id, Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $responseData = $this->employeeService->toggleEmployeeStatus($id, $data);
            return $this->responseFactory->createSuccessResponse($responseData);
        } catch (\InvalidArgumentException $e) {
            $this->logger->error("InvalidArgumentException caught in controller: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse($e->getMessage(), JsonResponse::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            $this->logger->error("Failed to toggle employee status: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to toggle employee status', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }



    // Удаление сотрудника по его ID
    #[Route('/{id}/delete', name: 'delete_id', methods: ['DELETE'])]
    public function deleteEmployee(int $id): JsonResponse
    {
        try {
            $result = $this->employeeService->deleteEmployee($id);
            return $this->responseFactory->createSuccessResponse($result);
        } catch (\InvalidArgumentException $e) {
            return $this->responseFactory->createNotFoundResponse($e->getMessage());
        } catch (\Exception $e) {
            $this->logger->error("Failed to delete employee with ID $id: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to delete employee', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    //Для демо данных
    #[Route('/seed', name: 'seed', methods: ['POST'])]
    public function seedEmployeeAndTranslations(): JsonResponse
    {
        try {
            $result = $this->employeeService->seedEmployees();
            return $this->responseFactory->createCreatedResponse($result);
        } catch (\Exception $e) {
            $this->logger->error("Failed to seed Staff and translations: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to seed Staff and translations', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}