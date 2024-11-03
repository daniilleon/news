<?php

namespace Module\Tests\Controller\Api;

use Module\Languages\Entity\Language;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Module\Employees\Entity\Employee;

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
    public function testGetSingleEmployee(): void
    {
        $entityManager = self::getContainer()->get('doctrine')->getManager();
        $language = $entityManager->getRepository(Language::class)->findOneBy(['LanguageCode' => 'EN']);
        if (!$language) {
            $language = new Language();
            $language->setLanguageCode('EN')->setLanguageName('English');
            $entityManager->persist($language);
            $entityManager->flush();
        }

        // Добавляем тестового сотрудника
        $employee = new Employee();
        $employee->setEmployeeName("Test Employee")
            ->setEmployeeLink("test-employee")
            ->setEmployeeJobTitle("Tester")
            ->setEmployeeDescription("Test description")
            ->setEmployeeCategoryID(1)
            ->setEmployeeLanguageID($language);

        $entityManager->persist($employee);
        $entityManager->flush();

        // Запрос на получение сотрудника
        $this->client->request('GET', '/api/employees/' . $employee->getEmployeeID());

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertNotNull($data);
        $this->assertArrayHasKey('Employee', $data, 'Response should contain "Employee" key');
        $this->assertEquals($employee->getEmployeeName(), $data['Employee']['EmployeeName'] ?? null);

        // Удаляем тестовые данные
        $entityManager->remove($employee);
        $entityManager->flush();
    }

    // Тест для добавления сотрудника с валидными данными
    public function testAddValidEmployee()
    {
        $this->client->request('POST', '/api/employees/add', [], [], [], json_encode([
            'EmployeeName' => 'Jane Smith',
            'EmployeeLink' => 'janesmith',
            'EmployeeJobTitle' => 'Developer',
            'EmployeeDescription' => 'Expert developer',
            'EmployeeCategoryID' => 1,
            'LanguageID' => 1
        ]));

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('Employee', $data);
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
            'EmployeeCategoryID' => 1,
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
        $this->client->request('POST', '/api/employees/add', [], [], [], json_encode([
            'EmployeeName' => 'Delete Test',
            'EmployeeLink' => 'deletetest',
            'EmployeeJobTitle' => 'Tester',
            'EmployeeDescription' => 'Testing delete',
            'EmployeeCategoryID' => 1,
            'LanguageID' => 1
        ]));

        $response = $this->client->getResponse();
        $data = json_decode($response->getContent(), true);
        $id = $data['Employee']['EmployeeID'] ?? null;
        $this->assertNotNull($id);

        $this->client->request('DELETE', '/api/employees/' . $id . '/delete');
        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('message', $data);
        $this->assertEquals("Employee with ID {$id} successfully deleted.", $data['message']);
    }

    // Тест для удаления несуществующего сотрудника
    public function testDeleteNonExistingEmployee()
    {
        $this->client->request('DELETE', '/api/employees/999/delete');
        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('error', $data);
        $this->assertEquals("Employee with ID 999 not found for deletion.", $data['error']);
    }

    // Тест для обновления существующего сотрудника с валидными данными
    public function testUpdateValidEmployee()
    {
        $this->client->request('POST', '/api/employees/add', [], [], [], json_encode([
            'EmployeeName' => 'Update Test',
            'EmployeeLink' => 'updatetest',
            'EmployeeJobTitle' => 'Tester',
            'EmployeeDescription' => 'Testing update',
            'EmployeeCategoryID' => 1,
            'LanguageID' => 1
        ]));

        $response = $this->client->getResponse();
        $data = json_decode($response->getContent(), true);
        $id = $data['Employee']['EmployeeID'] ?? null;
        $this->assertNotNull($id);

        $this->client->request('PUT', '/api/employees/update/' . $id, [], [], [], json_encode([
            'EmployeeName' => 'Updated Name'
        ]));

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertEquals('Employee updated successfully.', $data['message']);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->client = null;
    }
}
