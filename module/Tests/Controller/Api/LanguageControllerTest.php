<?php

namespace Module\Tests\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Component\HttpFoundation\Response;

class LanguageControllerTest extends WebTestCase
{
    private $client;

    protected function setUp(): void
    {
        parent::setUp();

        // Set up the client
        $this->client = static::createClient();

        // Set up database schema for testing
        $entityManager = $this->client->getContainer()->get('doctrine')->getManager();
        $schemaTool = new SchemaTool($entityManager);
        $metadata = $entityManager->getMetadataFactory()->getAllMetadata();

        // Drop and recreate the database schema
        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);
    }

    public function testGetLanguagesWhenEmpty(): void
    {
        $this->client->request('GET', '/api/languages');

        $this->assertEquals(Response::HTTP_CREATED, $this->client->getResponse()->getStatusCode());

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($responseData);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertEquals('No languages found. Added default language.', $responseData['message']);
    }

    public function testAddLanguage(): void
    {
        $this->client->request('POST', '/api/languages/add', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'code' => 'FR',
            'name' => 'French'
        ]));

        $this->assertEquals(Response::HTTP_CREATED, $this->client->getResponse()->getStatusCode());

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('id', $responseData);
        $this->assertEquals('French', $responseData['name']);
        $this->assertEquals('FR', $responseData['code']);
        $this->assertEquals('Language added successfully.', $responseData['message']);
    }

    public function testGetAllLanguages(): void
    {
        $this->client->request('POST', '/api/languages/add', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'code' => 'EN',
            'name' => 'English'
        ]));

        $this->client->request('GET', '/api/languages');

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($responseData);
        $this->assertNotEmpty($responseData);
    }

    public function testGetSingleLanguage(): void
    {
        $this->client->request('POST', '/api/languages/add', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'code' => 'EN',
            'name' => 'English'
        ]));

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('id', $responseData);
        $id = $responseData['id'];

        $this->client->request('GET', '/api/languages/' . $id);

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $languageData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('EN', $languageData['code']);
        $this->assertEquals('English', $languageData['name']);
    }

    public function testDeleteLanguage(): void
    {
        $this->client->request('POST', '/api/languages/add', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'code' => 'DE',
            'name' => 'German'
        ]));

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('id', $responseData);
        $id = $responseData['id'];

        $this->client->request('DELETE', '/api/languages/delete/' . $id);

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $deleteResponse = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals("Language with ID $id successfully deleted.", $deleteResponse['message']);
    }

    public function testUpdateLanguage(): void
    {
        // Создаем язык для обновления
        $this->client->request('POST', '/api/languages/add', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'code' => 'RU',
            'name' => 'Russian'
        ]));

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('id', $responseData);
        $id = $responseData['id'];

        // Обновляем язык с новыми данными
        $this->client->request('PUT', '/api/languages/update/' . $id, [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'code' => 'RU',
            'name' => 'Русский'
        ]));

        // Проверяем статус успешного обновления
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        // Проверяем, что данные языка были обновлены
        $updateResponseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('RU', $updateResponseData['language']['code']);
        $this->assertEquals('Русский', $updateResponseData['language']['name']);
        $this->assertEquals('Language updated successfully.', $updateResponseData['message']);
    }

    public function testUpdateLanguageValidationFailure(): void
    {
        // Создаем язык для обновления
        $this->client->request('POST', '/api/languages/add', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'code' => 'ES',
            'name' => 'Spanish'
        ]));

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('id', $responseData);
        $id = $responseData['id'];

        // Пытаемся обновить язык с некорректным значением имени
        $this->client->request('PUT', '/api/languages/update/' . $id, [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'code' => 'ES',
            'name' => 'Español 123'  // Некорректные символы и цифры
        ]));

        // Проверяем, что запрос вернул ошибку валидации
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $responseData);
        $this->assertEquals("Field 'name' must contain only letters without spaces.", $responseData['error']);
    }

    public function testUpdateLanguageMissingData(): void
    {
        // Создаем язык для обновления
        $this->client->request('POST', '/api/languages/add', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'code' => 'JP',
            'name' => 'Japanese'
        ]));

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('id', $responseData);
        $id = $responseData['id'];

        // Пытаемся обновить язык без данных
        $this->client->request('PUT', '/api/languages/update/' . $id, [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([]));

        // Проверяем, что запрос вернул ошибку из-за отсутствия данных
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $responseData);
        $this->assertEquals('No data provided for updating language', $responseData['error']);
    }
}