<?php

namespace Module\Employees\Controller\Api;

use Module\Employees\Service\EmployeeService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api')]
class EmployeeController
{
    private EmployeeService $employeeService;
    private LoggerInterface $logger;

    public function __construct(EmployeeService $employeeService, LoggerInterface $logger)
    {
        $this->employeeService = $employeeService;
        $this->logger = $logger;
        $this->logger->info("EmployeeController instance created.");
    }

    // Получение списка всех сотрудников
    #[Route('/employees', name: 'api_get_employees', methods: ['GET'])]
    public function getEmployees(): JsonResponse
    {
        $this->logger->info("Executing getEmployees method.");

        try {
            $employees = $this->employeeService->getAllEmployees();
            $employeesArray = array_map(function ($employee) {
                return [
                    'id' => $employee->getEmployeeID(),
                    'name' => $employee->getEmployeeName(),
                    'link' => $employee->getEmployeeLink(),
                    'jobTitle' => $employee->getEmployeeJobTitle(),
                    'description' => $employee->getEmployeeDescription(),
                    'social' => [
                        'linkedin' => $employee->getLinkedIn(),
                        'instagram' => $employee->getInstagram(),
                        'facebook' => $employee->getFacebook(),
                        'twitter' => $employee->getTwitter(),
                    ],
                    'categoryID' => $employee->getCategoryID(),
                    'languageID' => $employee->getLanguageID()
                ];
            }, $employees);

            return new JsonResponse(['employees' => $employeesArray], JsonResponse::HTTP_OK);
        } catch (\Exception $e) {
            $this->logger->error("Error in getEmployees method: " . $e->getMessage(), ['exception' => $e]);
            return new JsonResponse(['error' => 'Unable to fetch employees'], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Получение одного сотрудника по ID
    #[Route('/employees/{id}', name: 'api_get_employee', methods: ['GET'])]
    public function getEmployee(int $id): JsonResponse
    {
        $this->logger->info("Executing getEmployee method for ID: $id");

        try {
            $employee = $this->employeeService->getEmployeeById($id);

            if (!$employee) {
                $this->logger->info("Employee with ID $id not found.");
                return new JsonResponse(['error' => "Employee with ID $id not found."], JsonResponse::HTTP_NOT_FOUND);
            }

            $data = [
                'id' => $employee->getEmployeeID(),
                'name' => $employee->getEmployeeName(),
                'link' => $employee->getEmployeeLink(),
                'jobTitle' => $employee->getEmployeeJobTitle(),
                'description' => $employee->getEmployeeDescription(),
                'social' => [
                    'linkedin' => $employee->getLinkedIn(),
                    'instagram' => $employee->getInstagram(),
                    'facebook' => $employee->getFacebook(),
                    'twitter' => $employee->getTwitter(),
                ],
                'categoryID' => $employee->getCategoryID(),
                'languageID' => $employee->getLanguageID()
            ];

            return new JsonResponse($data, JsonResponse::HTTP_OK);
        } catch (\Exception $e) {
            $this->logger->error("Error in getEmployee method: " . $e->getMessage(), ['exception' => $e]);
            return new JsonResponse(['error' => 'Unable to fetch employee.'], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Добавление нового сотрудника
    #[Route('/employees/add', name: 'api_add_employee', methods: ['POST'])]
    public function addEmployee(Request $request): JsonResponse
    {
        $this->logger->info("Executing addEmployee method.");

        try {
            $data = json_decode($request->getContent(), true);

            // Передаем данные в сервис без проверки в контроллере
            $employee = $this->employeeService->addEmployee($data);

            $this->logger->info("Employee added successfully.", [
                'id' => $employee->getEmployeeID(),
                'name' => $employee->getEmployeeName(),
                'link' => $employee->getEmployeeLink()
            ]);

            return new JsonResponse(
                [
                    'id' => $employee->getEmployeeID(),
                    'name' => $employee->getEmployeeName(),
                    'link' => $employee->getEmployeeLink(),
                    'message' => 'Employee added successfully.'
                ],
                JsonResponse::HTTP_CREATED
            );
        } catch (\InvalidArgumentException $e) {
            // Обрабатываем ошибку валидации, выброшенную сервисом
            $this->logger->error("Validation error in addEmployee method: " . $e->getMessage());
            return new JsonResponse(['error' => $e->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            $this->logger->error("Error in addEmployee method: " . $e->getMessage(), ['exception' => $e]);
            return new JsonResponse(['error' => 'Unable to add employee'], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    // Обновление данных сотрудника по ID
    #[Route('/employees/update/{id}', name: 'api_update_employee', methods: ['PUT'])]
    public function updateEmployee(int $id, Request $request): JsonResponse
    {
        $this->logger->info("Executing updateEmployee method for ID: $id");

        try {
            $data = json_decode($request->getContent(), true);

            $employee = $this->employeeService->updateEmployee($id, $data);

            if (!$employee) {
                $this->logger->warning("Employee with ID $id not found for updating.");
                return new JsonResponse(['error' => "Employee with ID $id not found."], JsonResponse::HTTP_NOT_FOUND);
            }

            $this->logger->info("Employee with ID $id successfully updated.");
            return new JsonResponse([
                'id' => $employee->getEmployeeID(),
                'name' => $employee->getEmployeeName(),
                'link' => $employee->getEmployeeLink(),
                'message' => 'Employee updated successfully.'
            ], JsonResponse::HTTP_OK);
        } catch (\InvalidArgumentException $e) {
            $this->logger->error("Validation error in updateEmployee method: " . $e->getMessage());
            return new JsonResponse(['error' => $e->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            $this->logger->error("Error in updateEmployee method: " . $e->getMessage(), ['exception' => $e]);
            return new JsonResponse(['error' => 'Unable to update employee'], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Обновление конкретного поля сотрудника по ID
    #[Route('/employees/update-field/{id}', name: 'api_update_employee_field', methods: ['PATCH'])]
    public function updateEmployeeField(int $id, Request $request): JsonResponse
    {
        $this->logger->info("Executing updateEmployeeField method for ID: $id");

        try {
            $data = json_decode($request->getContent(), true);
            $field = $data['field'] ?? null;
            $value = $data['value'] ?? null;

            if (!$field || $value === null) {
                $this->logger->warning("Invalid data for updating field.", ['data' => $data]);
                return new JsonResponse(['error' => 'Invalid field data'], JsonResponse::HTTP_BAD_REQUEST);
            }

            $employee = $this->employeeService->updateEmployeeField($id, $field, $value);

            if (!$employee) {
                $this->logger->warning("Employee with ID $id not found for field update.");
                return new JsonResponse(['error' => "Employee with ID $id not found."], JsonResponse::HTTP_NOT_FOUND);
            }

            $this->logger->info("Employee with ID $id successfully updated for field $field.");
            return new JsonResponse([
                'id' => $employee->getEmployeeID(),
                'field' => $field,
                'value' => $value,
                'message' => 'Employee field updated successfully.'
            ], JsonResponse::HTTP_OK);
        } catch (\InvalidArgumentException $e) {
            $this->logger->error("Validation error in updateEmployeeField method: " . $e->getMessage());
            return new JsonResponse(['error' => $e->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            $this->logger->error("Error in updateEmployeeField method: " . $e->getMessage(), ['exception' => $e]);
            return new JsonResponse(['error' => 'Unable to update employee field'], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Удаление сотрудника по ID
    #[Route('/employees/delete/{id}', name: 'api_delete_employee', methods: ['DELETE'])]
    public function deleteEmployee(int $id): JsonResponse
    {
        $this->logger->info("Executing deleteEmployee method for ID: $id");

        try {
            $deleted = $this->employeeService->deleteEmployee($id);

            if ($deleted) {
                $this->logger->info("Employee with ID $id successfully deleted.");
                return new JsonResponse(['message' => "Employee with ID $id successfully deleted."], JsonResponse::HTTP_OK);
            } else {
                $this->logger->warning("Employee with ID $id not found for deletion.");
                return new JsonResponse(['error' => "Employee with ID $id not found."], JsonResponse::HTTP_NOT_FOUND);
            }
        } catch (\Exception $e) {
            $this->logger->error("Error in deleteEmployee method: " . $e->getMessage(), ['exception' => $e]);
            return new JsonResponse(['error' => 'Unable to delete employee.'], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}
