<?php

namespace Module\Tests\Controller\Api\Employees;

use Module\Categories\Entity\Categories;
use Module\Employees\EmployeesJobTitle\Service\EmployeesJobTitleService;
use Module\Employees\Entity\Employee;
use Module\Languages\Entity\Language;
use Module\Employees\EmployeesJobTitle\Entity\EmployeesJobTitle;
use Module\Languages\Service\LanguagesService;
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

        // Получаем EmployeesJobTitleService и выполняем предустановку значений
        $this->employeesJobTitleService = self::getContainer()->get(EmployeesJobTitleService::class);
        $this->employeesJobTitleService->seedJobTitlesAndTranslations();

        $this->languagesService = self::getContainer()->get(LanguagesService::class);
        $this->languagesService->seedLanguages();
    }

    private function clearDatabase(): void
    {
        $entityManager = self::getContainer()->get('doctrine')->getManager();
        $entityManager->createQuery('DELETE FROM Module\Employees\Entity\Employee')->execute();
        $entityManager->createQuery('DELETE FROM Module\Categories\Entity\Categories')->execute();
        $entityManager->createQuery('DELETE FROM Module\Languages\Entity\Language')->execute();
        $entityManager->createQuery('DELETE FROM Module\Employees\EmployeesJobTitle\Entity\EmployeesJobTitle')->execute();
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

    private function createTestJobTitle(): EmployeesJobTitle
    {
        $entityManager = self::getContainer()->get('doctrine')->getManager();
        $jobTitle = new EmployeesJobTitle();
        $jobTitle->setEmployeeJobTitleCode("DEV");
        $entityManager->persist($jobTitle);
        $entityManager->flush();

        return $jobTitle;
    }

    private function createTestEmployee(): Employee
    {
        $entityManager = self::getContainer()->get('doctrine')->getManager();
        $language = $this->createTestLanguage();
        $category = $this->createTestCategory();
        $jobTitle = $this->createTestJobTitle();

        $employee = new Employee();
        $employee->setEmployeeName("Test Employee")
            ->setEmployeeLink("test-employee")
            ->setEmployeeJobTitleID($jobTitle)
            ->setEmployeeDescription("Test description")
            ->setEmployeeCategoryID($category)
            ->setEmployeeLanguageID($language);

        $entityManager->persist($employee);
        $entityManager->flush();

        return $employee;
    }

    public function testAddEmployee()
    {
        $category = $this->createTestCategory();
        $language = $this->createTestLanguage();
        $jobTitle = $this->createTestJobTitle();

        $this->client->request('POST', '/api/employees/staff/add', [], [], [], json_encode([
            'EmployeeName' => 'Valid Employee',
            'EmployeeLink' => 'valid-employee',
            'EmployeeJobTitleID' => $jobTitle->getEmployeeJobTitleID(),
            'EmployeeDescription' => 'Expert developer',
            'CategoryID' => $category->getCategoryID(),
            'LanguageID' => $language->getLanguageID()
        ]));

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertEquals('success', $data['status']);
    }

    public function testAddEmployeeWithMissingRequiredFields()
    {
        $this->client->request('POST', '/api/employees/staff/add', [], [], [], json_encode([
            'EmployeeName' => '',
            'EmployeeLink' => '',
        ]));

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertEquals('error', $data['status']);
    }

    public function testUpdateEmployee()
    {
        $employee = $this->createTestEmployee();

        $this->client->request('PUT', '/api/employees/staff/update/' . $employee->getEmployeeID(), [], [], [], json_encode([
            'EmployeeName' => 'Updated Employee',
            'EmployeeLink' => 'updated-link'
        ]));

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertEquals('success', $data['status']);
        $this->assertEquals('Updated Employee', $data['data']['employee']['EmployeeName']);
    }

    public function testToggleEmployeeStatus()
    {
        $employee = $this->createTestEmployee();
        $employeeId = $employee->getEmployeeID();

        // Убедитесь, что статус сотрудника по умолчанию активен
        $this->assertTrue($employee->getEmployeeActive());

        // Попытка деактивировать сотрудника
        $this->client->request(
            'PUT',
            "/api/employees/staff/{$employeeId}/toggle-status",
            [],
            [],
            [],
            json_encode(['EmployeeActive' => false])
        );

        $response = $this->client->getResponse();
        $data = json_decode($response->getContent(), true);

        // Ожидаем успешный ответ 200 и проверяем, что `EmployeeActive` изменился на false
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode(), 'Expected status code 200');
        $this->assertEquals('success', $data['status']);
        $this->assertFalse($data['data']['employee']['EmployeeActive'], 'Employee should be inactive');

        // Теперь снова активируем сотрудника
        $this->client->request(
            'PUT',
            "/api/employees/staff/{$employeeId}/toggle-status",
            [],
            [],
            [],
            json_encode(['EmployeeActive' => true])
        );

        $response = $this->client->getResponse();
        $data = json_decode($response->getContent(), true);

        // Ожидаем успешный ответ 200 и проверяем, что `EmployeeActive` снова стал true
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode(), 'Expected status code 200');
        $this->assertEquals('success', $data['status']);
        $this->assertTrue($data['data']['employee']['EmployeeActive'], 'Employee should be active');
    }

    public function testDeleteEmployee()
    {
        $employee = $this->createTestEmployee();
        $employeeId = $employee->getEmployeeID();

        $this->client->request('DELETE', '/api/employees/staff/' . $employeeId . '/delete');

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertEquals('success', $data['status']);
    }

    public function testGetNonExistingEmployee()
    {
        $this->client->request('GET', '/api/employees/staff/9999');
        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }




}
