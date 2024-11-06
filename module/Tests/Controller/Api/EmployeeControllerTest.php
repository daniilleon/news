<?php

namespace Module\Tests\Controller\Api;

use Module\Languages\Entity\Language;
use Module\Categories\Entity\Categories;
use Module\Employees\Entity\Employee;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class EmployeeControllerTest extends WebTestCase
{
    private $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = self::createClient();
        $this->clearDatabase();
    }

    private function clearDatabase(): void
    {
        $entityManager = self::getContainer()->get('doctrine')->getManager();
        $entityManager->createQuery('DELETE FROM Module\Employees\Entity\Employee')->execute();
        $entityManager->createQuery('DELETE FROM Module\Categories\Entity\Categories')->execute();
        $entityManager->createQuery('DELETE FROM Module\Languages\Entity\Language')->execute();
        $entityManager->clear();
    }

    private function createTestCategory(): Categories
    {
        $entityManager = self::getContainer()->get('doctrine')->getManager();
        $category = new Categories();
        $category->setCategoryLink("test-category");
        $entityManager->persist($category);
        $entityManager->flush();

        return $category;
    }

    private function createTestLanguage(): Language
    {
        $entityManager = self::getContainer()->get('doctrine')->getManager();
        $language = $entityManager->getRepository(Language::class)->findOneBy(['LanguageCode' => 'EN']);
        if (!$language) {
            $language = new Language();
            $language->setLanguageCode('EN')->setLanguageName('English');
            $entityManager->persist($language);
            $entityManager->flush();
        }
        return $language;
    }

    private function createTestEmployee(): Employee
    {
        $entityManager = self::getContainer()->get('doctrine')->getManager();
        $language = $this->createTestLanguage();
        $category = $this->createTestCategory();

        $employee = new Employee();
        $employee->setEmployeeName("Test Employee")
            ->setEmployeeLink("test-employee")
            ->setEmployeeJobTitle("Tester")
            ->setEmployeeDescription("Test description")
            ->setEmployeeCategoryID($category)
            ->setEmployeeLanguageID($language);

        $entityManager->persist($employee);
        $entityManager->flush();

        return $employee;
    }

    public function testGetEmployeesEmpty()
    {
        $this->client->request('GET', '/api/employees');
        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('data', $data, 'Expected "data" key in the response.');
        $this->assertArrayHasKey('employees', $data['data'], 'Expected "employees" key in the response data.');
        $this->assertEmpty($data['data']['employees'], 'Employees list should be empty initially');
    }

    public function testGetEmployees()
    {
        $this->createTestEmployee();
        $this->client->request('GET', '/api/employees');
        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('data', $data, 'Expected "data" key in the response.');
        $this->assertArrayHasKey('employees', $data['data'], 'Expected "employees" key in the response data.');
        $this->assertNotEmpty($data['data']['employees']);
        $this->assertArrayHasKey('EmployeeID', $data['data']['employees'][0]);
    }

    public function testGetSingleEmployee(): void
    {
        $employee = $this->createTestEmployee();
        $this->client->request('GET', '/api/employees/' . $employee->getEmployeeID());
        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('data', $data, 'Expected "data" key in the response.');
        $this->assertArrayHasKey('employee', $data['data'], 'Expected "employee" key in the response data.');
        $this->assertEquals($employee->getEmployeeName(), $data['data']['employee']['EmployeeName']);
    }

    public function testGetNonExistingEmployee(): void
    {
        $this->client->request('GET', '/api/employees/9999');
        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertEquals('error', $data['status']);
        $this->assertEquals("Employee with ID 9999 not found.", $data['message']);
    }

    public function testAddEmployee()
    {
        $category = $this->createTestCategory();
        $language = $this->createTestLanguage();

        $this->client->request('POST', '/api/employees/add', [], [], [], json_encode([
            'EmployeeName' => 'Valid Employee',
            'EmployeeLink' => 'valid-employee',
            'EmployeeJobTitle' => 'Developer',
            'EmployeeDescription' => 'Expert developer',
            'CategoryID' => $category->getCategoryID(),
            'LanguageID' => $language->getLanguageID()
        ]));

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertEquals('success', $data['status']);
        $this->assertArrayHasKey('data', $data, 'Expected "data" key in the response.');
        $this->assertArrayHasKey('employee', $data['data'], 'Expected "employee" key in the response data.');
        $this->assertEquals('Employee added successfully.', $data['message']);
    }

    public function testAddEmployeeInvalidData()
    {
        $this->client->request('POST', '/api/employees/add', [], [], [], json_encode([
            'EmployeeName' => 'Invalid123',
            'EmployeeLink' => 'invalid-link',
            'EmployeeJobTitle' => '123',
            'EmployeeDescription' => 'Description',
            'CategoryID' => 1,
            'LanguageID' => 1
        ]));

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertEquals('error', $data['status']);
        $this->assertEquals("Field 'EmployeeName' can contain only letters and spaces.", $data['message']);
    }

    public function testUpdateEmployee()
    {
        $employee = $this->createTestEmployee();

        $this->client->request('PUT', '/api/employees/update/' . $employee->getEmployeeID(), [], [], [], json_encode([
            'EmployeeName' => 'Updated Employee'
        ]));

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertEquals('success', $data['status']);
        $this->assertEquals('Employee updated successfully.', $data['message']);
        $this->assertArrayHasKey('data', $data, 'Expected "data" key in the response.');
        $this->assertEquals('Updated Employee', $data['data']['employee']['EmployeeName']);
    }

    public function testDeleteEmployee(): void
    {
        // Создаем тестового сотрудника и получаем его ID
        $employee = $this->createTestEmployee();
        $employeeId = $employee->getEmployeeID();

        // Отправляем DELETE-запрос на удаление сотрудника
        $this->client->request('DELETE', '/api/employees/' . $employeeId . '/delete');

        // Проверка HTTP-статуса ответа
        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        // Декодируем JSON-ответ
        $data = json_decode($response->getContent(), true);

        // Проверка структуры и содержания ответа
        $this->assertArrayHasKey('status', $data, 'Expected "status" key in the response.');
        $this->assertArrayHasKey('message', $data, 'Expected "message" key in the response.');
        $this->assertEquals('success', $data['status'], 'Expected status to be "success".');

        // Проверка на включение ID сотрудника в сообщение
        $expectedMessage = "Employee with ID {$employeeId} successfully deleted.";
        $this->assertEquals($expectedMessage, $data['message'], 'Expected deletion message to exactly match the expected format.');
    }


}
