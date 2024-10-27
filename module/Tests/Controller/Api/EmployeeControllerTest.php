<?php

namespace Module\Tests\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class EmployeeControllerTest extends WebTestCase
{
    private $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    // Тест для получения списка сотрудников
    public function testGetEmployees()
    {
        $this->client->request('GET', '/api/employees');
        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('employees', $data, 'Response should contain "employees" key');
        $this->assertIsArray($data['employees'], '"employees" should be an array');
    }

    // Тест для получения отдельного сотрудника
    public function testGetSingleEmployee()
    {
        // Сначала добавляем сотрудника, чтобы его можно было получить
        $this->client->request('POST', '/api/employees/add', [], [], [], json_encode([
            'EmployeeName' => 'John Doe',
            'EmployeeLink' => 'johndoe',
            'EmployeeJobTitle' => 'Engineer',
            'EmployeeDescription' => 'Experienced engineer',
            'CategoryID' => 1,
            'LanguageID' => 1
        ]));

        $response = $this->client->getResponse();
        $data = json_decode($response->getContent(), true);
        $id = $data['id'] ?? null;
        $this->assertNotNull($id);

        // Запрос на получение сотрудника по ID
        $this->client->request('GET', '/api/employees/' . $id);
        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('id', $data);
        $this->assertEquals($id, $data['id']);
        $this->assertEquals('John Doe', $data['name']);
    }

    // Тест для добавления сотрудника с валидными данными
    public function testAddValidEmployee()
    {
        $this->client->request('POST', '/api/employees/add', [], [], [], json_encode([
            'EmployeeName' => 'Jane Smith',
            'EmployeeLink' => 'janesmith',
            'EmployeeJobTitle' => 'Developer',
            'EmployeeDescription' => 'Expert developer',
            'CategoryID' => 1,
            'LanguageID' => 1
        ]));

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('message', $data);
        $this->assertEquals('Employee added successfully.', $data['message']);
    }

    // Тест для добавления сотрудника с невалидными данными (имя содержит цифры)
    public function testAddEmployeeWithInvalidName()
    {
        $this->client->request('POST', '/api/employees/add', [], [], [], json_encode([
            'EmployeeName' => 'Jane123', // Неправильное имя
            'EmployeeLink' => 'janesmith',
            'EmployeeJobTitle' => 'Developer',
            'EmployeeDescription' => 'Expert developer',
            'CategoryID' => 1,
            'LanguageID' => 1
        ]));

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode(), 'Expected HTTP 400 Bad Request for invalid name');

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('error', $data);
        $this->assertEquals("Field 'EmployeeName' can contain only letters and spaces.", $data['error']);
    }

    // Тест для удаления существующего сотрудника
    public function testDeleteEmployee()
    {
        // Добавляем сотрудника для теста удаления
        $this->client->request('POST', '/api/employees/add', [], [], [], json_encode([
            'EmployeeName' => 'Delete Test',
            'EmployeeLink' => 'deletetest',
            'EmployeeJobTitle' => 'Tester',
            'EmployeeDescription' => 'Testing delete',
            'CategoryID' => 1,
            'LanguageID' => 1
        ]));

        $response = $this->client->getResponse();
        $data = json_decode($response->getContent(), true);
        $id = $data['id'] ?? null;
        $this->assertNotNull($id);

        // Удаляем сотрудника
        $this->client->request('DELETE', '/api/employees/delete/' . $id);
        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('message', $data);
        $this->assertEquals("Employee with ID {$id} successfully deleted.", $data['message']);
    }

    // Тест для удаления несуществующего сотрудника
    public function testDeleteNonExistingEmployee()
    {
        $this->client->request('DELETE', '/api/employees/delete/99999'); // Несуществующий ID
        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('error', $data);
        $this->assertEquals("Employee with ID 99999 not found.", $data['error']);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->client = null;
    }
}
