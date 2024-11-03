<?php
namespace Module\Employees\Controller\Api;

use Exception;
use InvalidArgumentException;
use Module\Employees\Service\EmployeesService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Module\Common\Factory\ResponseFactory;

// Основной контроллер API для работы с сотрудниками.
#[Route('/api')]
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
    #[Route('/employees', name: 'api_get_employees', methods: ['GET'])]
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
    #[Route('/employees/{id}', name: 'api_get_employee', methods: ['GET'])]
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
    #[Route('/employees/add', name: 'api_add_employee', methods: ['POST'])]
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
    #[Route('/employees/update/{id}', name: 'api_update_employee', methods: ['PUT'])]
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


    // Удаление сотрудника по его ID
    #[Route('/employees/{id}/delete', name: 'api_delete_employee', methods: ['DELETE'])]
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

}