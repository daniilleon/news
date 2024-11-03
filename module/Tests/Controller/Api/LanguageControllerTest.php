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
        $language = new Language();
        $language->setLanguageCode('MYN');
        $language->setLanguageName('Demo');

        $this->languagesService->expects($this->once())
            ->method('addLanguage')
            ->willReturn([
                'message' => 'Language added successfully.',
                'language' => [
                    'LanguageID' => 1,
                    'LanguageCode' => 'MYN',
                    'LanguageName' => 'Demo'
                ]
            ]);

        $request = new Request([], [], [], [], [], [], json_encode([
            'LanguageCode' => 'MYN',
            'LanguageName' => 'Demo'
        ]));

        $response = $this->controller->addLanguage($request);
        $this->assertEquals(JsonResponse::HTTP_CREATED, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('language', $responseData);
        $this->assertEquals('Language added successfully.', $responseData['message']);
    }

    public function testAddLanguageWithExcessiveLanguageCode(): void
    {
        $this->languagesService->method('addLanguage')
            ->will($this->throwException(new \InvalidArgumentException("LanguageCode cannot contain more than 3 characters.")));

        $request = new Request([], [], [], [], [], [], json_encode([
            'LanguageCode' => 'LONG',
            'LanguageName' => 'ValidName'
        ]));

        $response = $this->controller->addLanguage($request);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('error', $responseData);
        $this->assertEquals("LanguageCode cannot contain more than 3 characters.", $responseData['error']);
    }

    public function testAddLanguageWithEmptyLanguageCode(): void
    {
        $this->languagesService->method('addLanguage')
            ->will($this->throwException(new \InvalidArgumentException("LanguageCode cannot be empty or only spaces.")));

        $request = new Request([], [], [], [], [], [], json_encode([
            'LanguageCode' => '',
            'LanguageName' => 'ValidName'
        ]));

        $response = $this->controller->addLanguage($request);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('error', $responseData);
        $this->assertEquals("LanguageCode cannot be empty or only spaces.", $responseData['error']);
    }

    public function testAddLanguageWithEmptyLanguageName(): void
    {
        $this->languagesService->method('addLanguage')
            ->will($this->throwException(new \InvalidArgumentException("LanguageName cannot be empty or only spaces.")));

        $request = new Request([], [], [], [], [], [], json_encode([
            'LanguageCode' => 'MYN',
            'LanguageName' => ''
        ]));

        $response = $this->controller->addLanguage($request);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('error', $responseData);
        $this->assertEquals("LanguageName cannot be empty or only spaces.", $responseData['error']);
    }

    public function testAddLanguageWithInvalidCharacterInLanguageName(): void
    {
        $this->languagesService->method('addLanguage')
            ->will($this->throwException(new \InvalidArgumentException("LanguageName must contain only letters without spaces.")));

        $request = new Request([], [], [], [], [], [], json_encode([
            'LanguageCode' => 'MYN',
            'LanguageName' => 'Demo123'
        ]));

        $response = $this->controller->addLanguage($request);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('error', $responseData);
        $this->assertEquals("LanguageName must contain only letters without spaces.", $responseData['error']);
    }

    public function testUpdateLanguage(): void
    {
        $updatedLanguage = [
            'language' => [
                'LanguageID' => 8,
                'LanguageCode' => 'MYY',
                'LanguageName' => 'Demo'
            ],
            'message' => 'Language updated successfully.'
        ];

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
        $this->assertEquals($updatedLanguage, $responseData);
    }

    public function testDeleteLanguage(): void
    {
        $this->languagesService->expects($this->once())
            ->method('deleteLanguage')
            ->with(10)
            ->willReturn([
                'message' => "Language with ID 10 successfully deleted."
            ]);

        $response = $this->controller->deleteLanguage(10);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertEquals("Language with ID 10 successfully deleted.", $responseData['message']);
    }

    public function testLanguageNotFound(): void
    {
        $this->languagesService->method('getLanguageById')
            ->will($this->throwException(new \InvalidArgumentException("Language with ID 10 not found.")));

        $response = $this->controller->getLanguage(10);
        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('error', $responseData);
        $this->assertEquals("Language with ID 10 not found.", $responseData['error']);
    }

    public function testGetAllLanguagesWithNoLanguagesAvailable(): void
    {
        $this->languagesService->expects($this->once())
            ->method('getAllLanguages')
            ->willReturn(['message' => 'No languages found in the database.']);

        $response = $this->controller->getLanguages();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertEquals("No languages found in the database.", $responseData['message']);
    }
}
