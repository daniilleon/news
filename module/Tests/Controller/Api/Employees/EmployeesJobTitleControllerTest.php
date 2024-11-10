<?php

namespace Module\Tests\Controller\Api\Employees;

use Module\Employees\EmployeesJobTitle\Controller\Api\EmployeesJobTitleController;
use Module\Employees\EmployeesJobTitle\Service\EmployeesJobTitleService;
use Module\Common\Factory\ResponseFactory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Psr\Log\LoggerInterface;

class EmployeesJobTitleControllerTest extends WebTestCase
{
    private EmployeesJobTitleService $jobTitleService;
    private ResponseFactory $responseFactory;
    private LoggerInterface $logger;
    private EmployeesJobTitleController $controller;

    protected function setUp(): void
    {
        $this->jobTitleService = $this->createMock(EmployeesJobTitleService::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->responseFactory = new ResponseFactory($this->logger);

        $this->controller = new EmployeesJobTitleController(
            $this->jobTitleService,
            $this->logger,
            $this->responseFactory
        );
    }

    // Тест на успешное добавление новой должности
    public function testAddJobTitle(): void
    {
        $jobTitleData = [
            'employeeJobTitle' => [
                'EmployeeJobTitleID' => 1,
                'EmployeeJobTitleCode' => 'DEV',
            ],
            'message' => 'Job title added successfully.'
        ];

        $this->jobTitleService->method('createEmployeeJobTitle')
            ->willReturn($jobTitleData);

        $request = new Request([], [], [], [], [], [], json_encode(['EmployeeJobTitleCode' => 'DEV']));
        $response = $this->controller->addEmployeeJobTitle($request);

        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('success', $responseData['status']);
        $this->assertEquals('Job title added successfully.', $responseData['message']);
    }

    // Тест на добавление должности с дублирующимся кодом
    public function testAddDuplicateJobTitleCode(): void
    {
        $this->jobTitleService->method('createEmployeeJobTitle')
            ->will($this->throwException(new \InvalidArgumentException("EmployeeJobTitleCode 'DEV' already exists.")));

        $request = new Request([], [], [], [], [], [], json_encode(['EmployeeJobTitleCode' => 'DEV']));
        $response = $this->controller->addEmployeeJobTitle($request);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('error', $responseData['status']);
        $this->assertEquals("EmployeeJobTitleCode 'DEV' already exists.", $responseData['message']);
    }

    // Тест на добавление перевода для должности
    public function testAddJobTitleTranslation(): void
    {
        $translationData = [
            'employeeJobTitle' => [
                'EmployeeJobTitleID' => 1,
                'EmployeeJobTitleCode' => 'DEV',
            ],
            'translation' => [
                'TranslationID' => 1,
                'LanguageID' => 2,
                'EmployeeJobTitleName' => 'Developer'
            ],
            'message' => 'Job title translation added successfully.'
        ];

        $this->jobTitleService->method('createEmployeeJobTitleTranslation')
            ->willReturn($translationData);

        $request = new Request([], [], [], [], [], [], json_encode(['LanguageID' => 2, 'EmployeeJobTitleName' => 'Developer']));
        $response = $this->controller->addEmployeeJobTitleTranslation(1, $request);

        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('success', $responseData['status']);
        $this->assertEquals('Job title translation added successfully.', $responseData['message']);
    }

    // Тест на добавление перевода с дублирующимся названием
    public function testAddDuplicateJobTitleTranslationName(): void
    {
        $this->jobTitleService->method('createEmployeeJobTitleTranslation')
            ->will($this->throwException(new \InvalidArgumentException("EmployeeJobTitleName 'Developer' already exists for this JobTitle.")));

        $request = new Request([], [], [], [], [], [], json_encode(['LanguageID' => 2, 'EmployeeJobTitleName' => 'Developer']));
        $response = $this->controller->addEmployeeJobTitleTranslation(1, $request);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('error', $responseData['status']);
        $this->assertEquals("EmployeeJobTitleName 'Developer' already exists for this JobTitle.", $responseData['message']);
    }

    // Тест на обновление кода должности
    public function testUpdateJobTitleCode(): void
    {
        $updatedJobTitleData = [
            'employeeJobTitle' => [
                'EmployeeJobTitleID' => 1,
                'EmployeeJobTitleCode' => 'UPDATED',
            ],
            'message' => 'Job title code updated successfully.'
        ];

        $this->jobTitleService->method('updateEmployeeJobTitleCode')
            ->willReturn($updatedJobTitleData);

        $request = new Request([], [], [], [], [], [], json_encode(['EmployeeJobTitleCode' => 'UPDATED']));
        $response = $this->controller->updateEmployeeJobTitleCode(1, $request);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('success', $responseData['status']);
        $this->assertEquals('Job title code updated successfully.', $responseData['message']);
    }

    // Тест на обновление несуществующего кода должности
    public function testUpdateNonExistingJobTitleCode(): void
    {
        // Настроим сервис для возврата ошибки при отсутствии должности
        $this->jobTitleService->method('updateEmployeeJobTitleCode')
            ->will($this->throwException(new \InvalidArgumentException("EmployeeJobTitle with ID 99 not found.")));

        $request = new Request([], [], [], [], [], [], json_encode(['EmployeeJobTitleCode' => 'NON_EXISTENT']));
        $response = $this->controller->updateEmployeeJobTitleCode(99, $request);

        // Ожидаем, что вернется 404, как указано в вашей логике
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('error', $responseData['status']);
        $this->assertEquals("EmployeeJobTitle with ID 99 not found.", $responseData['message']);
    }

    // Тест на обновление должности без EmployeeJobTitleCode в базе данных
    public function testUpdateJobTitleWithoutCode(): void
    {
        // Настраиваем сервис, чтобы выбрасывать исключение, если EmployeeJobTitleCode отсутствует
        $this->jobTitleService->method('updateEmployeeJobTitleCode')
            ->will($this->throwException(new \InvalidArgumentException("Field 'EmployeeJobTitleCode' is required and cannot be empty.")));

        $request = new Request([], [], [], [], [], [], json_encode(['EmployeeJobTitleCode' => 'NewCode']));

        // Пытаемся обновить должность, у которой отсутствует EmployeeJobTitleCode
        $response = $this->controller->updateEmployeeJobTitleCode(1, $request);

        // Проверяем, что вернулся статус 400 и корректное сообщение об ошибке
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('error', $responseData['status']);
        $this->assertEquals("Field 'EmployeeJobTitleCode' is required and cannot be empty.", $responseData['message']);
    }

// Тест на обновление перевода должности без EmployeeJobTitleName в базе данных
    public function testUpdateJobTitleTranslationWithoutName(): void
    {
        // Настраиваем сервис, чтобы выбрасывать исключение, если EmployeeJobTitleName отсутствует
        $this->jobTitleService->method('updateEmployeeJobTitleTranslation')
            ->will($this->throwException(new \InvalidArgumentException("Field 'EmployeeJobTitleName' is required and cannot be empty.")));

        $request = new Request([], [], [], [], [], [], json_encode(['EmployeeJobTitleName' => 'NewTranslationName']));

        // Пытаемся обновить перевод должности, у которой отсутствует EmployeeJobTitleName
        $response = $this->controller->updateEmployeeJobTitleTranslation(1, 1, $request);

        // Проверяем, что вернулся статус 400 и корректное сообщение об ошибке
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('error', $responseData['status']);
        $this->assertEquals("Field 'EmployeeJobTitleName' is required and cannot be empty.", $responseData['message']);
    }


    // Тест на удаление должности
    public function testDeleteJobTitle(): void
    {
        $this->jobTitleService->method('deleteEmployeeJobTitle')
            ->with(1)
            ->willReturn(['message' => "EmployeeJobTitle with ID 1 and its translations successfully deleted."]);

        $response = $this->controller->deleteEmployeeJobTitle(1);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('success', $responseData['status']);
        $this->assertEquals("EmployeeJobTitle with ID 1 and its translations successfully deleted.", $responseData['message']);
    }

    // Тест на удаление перевода должности
    public function testDeleteJobTitleTranslation(): void
    {
        $this->jobTitleService->method('deleteEmployeeJobTitleTranslation')
            ->with(1, 2)
            ->willReturn(['message' => "Translation with ID 2 successfully deleted for EmployeeJobTitle ID 1."]);

        $response = $this->controller->deleteEmployeeJobTitleTranslation(1, 2);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('success', $responseData['status']);
        $this->assertEquals("Translation with ID 2 successfully deleted for EmployeeJobTitle ID 1.", $responseData['message']);
    }
}
