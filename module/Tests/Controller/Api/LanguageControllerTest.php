<?php

namespace Module\Tests\Controller\Api;

use Module\Languages\Controller\Api\LanguagesController;
use Module\Languages\Service\LanguagesService;
use Module\Common\Factory\ResponseFactory;
use Module\Languages\Entity\Language;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class LanguageControllerTest extends WebTestCase
{
    private $languagesService;
    private $responseFactory;
    private $logger;
    private $controller;

    protected function setUp(): void
    {
        $this->languagesService = $this->createMock(LanguagesService::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->responseFactory = new ResponseFactory($this->logger);

        $this->controller = new LanguagesController(
            $this->languagesService,
            $this->logger,
            $this->responseFactory
        );
    }

    public function testAddLanguage(): void
    {
        // Создаем объект Language и устанавливаем ID через Reflection
        $language = new Language();
        $reflection = new \ReflectionClass($language);
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($language, 1);  // Устанавливаем ID вручную для теста

        $language->setLanguageCode('MYN');
        $language->setLanguageName('Demo');

        // Ожидаем вызова addLanguage и возвращаем подготовленный объект
        $this->languagesService->expects($this->once())
            ->method('addLanguage')
            ->with('MYN', 'Demo')
            ->willReturn($language);

        // Создаем запрос
        $request = new Request([], [], [], [], [], [], json_encode([
            'LanguageCode' => 'MYN',
            'LanguageName' => 'Demo'
        ]));

        // Выполняем метод контроллера
        $response = $this->controller->addLanguage($request);

        // Проверяем код ответа и содержимое
        $this->assertEquals(JsonResponse::HTTP_CREATED, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);

        // Добавляем временный вывод для диагностики
        var_dump($responseData);

        $this->assertArrayHasKey('LanguageID', $responseData);
        $this->assertEquals(1, $responseData['LanguageID']);
        $this->assertEquals('MYN', $responseData['LanguageCode']);
        $this->assertEquals('Demo', $responseData['LanguageName']);
        $this->assertEquals('Language added successfully.', $responseData['message']);
    }





    public function testUpdateLanguage(): void
    {
        $updatedLanguage = new Language(8, 'MYY', 'Demo');

        $this->languagesService->expects($this->once())
            ->method('updateLanguage')
            ->with(8, ['LanguageCode' => 'MYY', 'LanguageName' => 'Demo'])
            ->willReturn($updatedLanguage);

        $request = new Request([], [], [], [], [], [], json_encode([
            'LanguageCode' => 'MYY',
            'LanguageName' => 'Demo'
        ]));

        $response = $this->controller->updateLanguage(8, $request);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('LanguageID', $responseData);
        $this->assertArrayHasKey('LanguageCode', $responseData);
        $this->assertArrayHasKey('LanguageName', $responseData);
        $this->assertArrayHasKey('message', $responseData);

        $this->assertEquals(8, $responseData['LanguageID']);
        $this->assertEquals('MYY', $responseData['LanguageCode']);
        $this->assertEquals('Demo', $responseData['LanguageName']);
        $this->assertEquals('Language updated successfully.', $responseData['message']);
    }

    public function testLanguageAlreadyExists(): void
    {
        $this->languagesService->method('addLanguage')
            ->will($this->throwException(new \InvalidArgumentException("Language with code 'myy' already exists.")));

        $request = new Request([], [], [], [], [], [], json_encode([
            'LanguageCode' => 'myy',
            'LanguageName' => 'Demo'
        ]));

        $response = $this->controller->addLanguage($request);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('error', $responseData);
        $this->assertEquals("Language with code 'myy' already exists.", $responseData['error']);
    }

    public function testDeleteLanguage(): void
    {
        $this->languagesService->expects($this->once())
            ->method('deleteLanguage')
            ->with(10)
            ->willReturn(true);

        $response = $this->controller->deleteLanguage(10);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertEquals("Language with ID 10 successfully deleted.", $responseData['message']);
    }

    public function testLanguageNotFound(): void
    {
        $this->languagesService->method('getLanguageById')
            ->with(10)
            ->willReturn(null);

        $response = $this->controller->getLanguage(10);
        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('error', $responseData);
        $this->assertEquals("Language with ID 10 not found.", $responseData['error']);
    }
}
