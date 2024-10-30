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
        $this->logger->info("Executing getEmployees method.");
        try {
            $employees = $this->employeeService->getAllEmployees();
            $employeesArray = array_map([$this->employeeService, 'formatEmployeeData'], $employees);

            $this->logger->info("Successfully fetched employee list.");
            return $this->responseFactory->createSuccessResponse(['employees' => $employeesArray]);
        } catch (InvalidArgumentException $e) {
            $this->logger->error($e->getMessage());
            return $this->responseFactory->createErrorResponse($e->getMessage(), JsonResponse::HTTP_BAD_REQUEST);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to fetch employees', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    // Получение данных сотрудника по его ID.
    #[Route('/employees/{id}', name: 'api_get_employee', methods: ['GET'])]
    public function getEmployee(int $id): JsonResponse
    {
        try {
            $employee = $this->employeeService->getEmployeeById($id);

            if (!$employee) {
                $this->logger->warning("Employee with ID $id not found.");
                return $this->responseFactory->createNotFoundResponse("Employee with ID $id not found.");
            }

            $data = $this->employeeService->formatEmployeeData($employee);
            $this->logger->info("Successfully fetched employee data for ID: $id");
            return $this->responseFactory->createSuccessResponse($data);
        } catch (InvalidArgumentException $e) {
            // Ошибка уже залогирована в сервисе, просто возвращаем ответ
            return $this->responseFactory->createErrorResponse($e->getMessage(), JsonResponse::HTTP_BAD_REQUEST);
        } catch (Exception $e) {
            // Логируем общую ошибку
            $this->logger->error("Unable to update employee: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to update employee', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Добавление нового сотрудника.
    #[Route('/employees/add', name: 'api_add_employee', methods: ['POST'])]
    public function addEmployee(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $employee = $this->employeeService->addEmployee($data);

            $responseData = $this->employeeService->formatEmployeeData($employee);
            $responseData['message'] = 'Employee added successfully.';

            $this->logger->info("Successfully added employee with ID: " . $employee->getEmployeeID());
            return $this->responseFactory->createCreatedResponse($responseData);
        } catch (InvalidArgumentException $e) {
            // Ошибка уже залогирована в сервисе, просто возвращаем ответ
            return $this->responseFactory->createErrorResponse($e->getMessage(), JsonResponse::HTTP_BAD_REQUEST);
        } catch (Exception $e) {
            // Логируем общую ошибку
            $this->logger->error("Unable to update employee: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to update employee', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Обновление данных сотрудника по его ID.
    #[Route('/employees/update/{id}', name: 'api_update_employee', methods: ['PUT'])]
    public function updateEmployee(int $id, Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $employee = $this->employeeService->updateEmployee($id, $data);

            if (!$employee) {
                return $this->responseFactory->createNotFoundResponse("Employee with ID $id not found.");
            }

            $responseData = $this->employeeService->formatEmployeeData($employee);
            $responseData['message'] = 'Employee updated successfully.';

            return $this->responseFactory->createSuccessResponse($responseData);

        } catch (InvalidArgumentException $e) {
            // Ошибка уже залогирована в сервисе, просто возвращаем ответ
            return $this->responseFactory->createErrorResponse($e->getMessage(), JsonResponse::HTTP_BAD_REQUEST);
        } catch (Exception $e) {
            // Логируем общую ошибку
            $this->logger->error("Unable to update employee: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to update employee', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Обновление одного конкретного поля сотрудника по его ID.
    #[Route('/employees/update-field/{id}', name: 'api_update_employee_field', methods: ['PATCH'])]
    public function updateEmployeeField(int $id, Request $request): JsonResponse
    {
        $this->logger->info("Executing updateEmployeeField method for ID: $id");

        try {
            $data = json_decode($request->getContent(), true);
            $field = $data['field'] ?? null;
            $value = $data['value'] ?? null;

            // Обновляем конкретное поле сотрудника
            $employee = $this->employeeService->updateEmployeeField($id, $field, $value);

            if (!$employee) {
                return $this->responseFactory->createNotFoundResponse("Employee with ID $id not found.");
            }

            // Формируем ответ с информацией об обновленном поле
            $updatedFieldValue = ($field === 'language')
                ? $this->employeeService->formatEmployeeData($employee)['language']
                : $value;

            return $this->responseFactory->createSuccessResponse([
                'id' => $employee->getEmployeeID(),
                'field' => $field,
                'value' => $updatedFieldValue,
                'message' => 'Employee field updated successfully.'
            ]);
        } catch (InvalidArgumentException $e) {
            // Исключение уже залогировано в сервисе, просто возвращаем ответ
            return $this->responseFactory->createErrorResponse($e->getMessage(), JsonResponse::HTTP_BAD_REQUEST);
        } catch (Exception $e) {
            // Логируем только общие ошибки
            $this->logger->error("Unable to update employee field: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to update employee field', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    // Удаление сотрудника по его ID.
    #[Route('/employees/delete/{id}', name: 'api_delete_employee', methods: ['DELETE'])]
    public function deleteEmployee(int $id): JsonResponse
    {
        try {
            $deleted = $this->employeeService->deleteEmployee($id);

            if ($deleted) {
                return $this->responseFactory->createSuccessResponse([
                    'message' => "Employee with ID $id successfully deleted."
                ]);
            } else {
                $this->logger->warning("Employee with ID $id not found for deletion.");
                return $this->responseFactory->createNotFoundResponse("Employee with ID $id not found.");
            }
        } catch (InvalidArgumentException $e) {
            // Исключение уже залогировано в сервисе, просто возвращаем ответ
            return $this->responseFactory->createNotFoundResponse($e->getMessage());
        } catch (Exception $e) {
            // Логируем только общие ошибки
            $this->logger->error("Unable to delete employee: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to delete employee', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}