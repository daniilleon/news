<?php

namespace Module\Tests\Controller\Api;

use Module\Languages\Controller\Api\LanguagesController;
use Module\Languages\Service\LanguagesService;
use Module\Common\Factory\ResponseFactory;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class LanguageControllerTest extends WebTestCase
{
    private LanguagesService $languagesService;
    private LoggerInterface $logger;
    private ResponseFactory $responseFactory;
    private LanguagesController $controller;

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
        $data = [
            'LanguageCode' => 'ENG',
            'LanguageName' => 'English'
        ];

        $this->languagesService->expects($this->once())
            ->method('addLanguage')
            ->with($data)
            ->willReturn([
                'message' => 'Language added successfully.',
                'language' => [
                    'LanguageID' => 1,
                    'LanguageCode' => 'ENG',
                    'LanguageName' => 'English'
                ]
            ]);

        $request = new Request([], [], [], [], [], [], json_encode($data));
        $response = $this->controller->addLanguage($request);

        $this->assertEquals(JsonResponse::HTTP_CREATED, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('status', $responseData);
        $this->assertEquals('success', $responseData['status']);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertArrayHasKey('data', $responseData);
        $this->assertArrayHasKey('language', $responseData['data']);
    }

    public function testAddLanguageWithInvalidData(): void
    {
        $this->languagesService->method('addLanguage')
            ->will($this->throwException(new \InvalidArgumentException("LanguageCode cannot be empty or only spaces.")));

        $request = new Request([], [], [], [], [], [], json_encode([
            'LanguageCode' => '',
            'LanguageName' => 'ValidName'
        ]));

        $response = $this->controller->addLanguage($request);

        $this->assertEquals(JsonResponse::HTTP_BAD_REQUEST, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('status', $responseData);
        $this->assertEquals('error', $responseData['status']);
        $this->assertArrayHasKey('message', $responseData);
    }

    public function testUpdateLanguage(): void
    {
        $data = [
            'LanguageCode' => 'SPA',
            'LanguageName' => 'Spanish'
        ];

        $this->languagesService->expects($this->once())
            ->method('updateLanguage')
            ->with(1, $data)
            ->willReturn([
                'message' => 'Language updated successfully.',
                'language' => [
                    'LanguageID' => 1,
                    'LanguageCode' => 'SPA',
                    'LanguageName' => 'Spanish'
                ]
            ]);

        $request = new Request([], [], [], [], [], [], json_encode($data));
        $response = $this->controller->updateLanguage(1, $request);

        $this->assertEquals(JsonResponse::HTTP_OK, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('status', $responseData);
        $this->assertEquals('success', $responseData['status']);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertArrayHasKey('data', $responseData);
        $this->assertArrayHasKey('language', $responseData['data']);
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
        $this->assertEquals(JsonResponse::HTTP_OK, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('status', $responseData);
        $this->assertEquals('success', $responseData['status']);
        $this->assertArrayHasKey('message', $responseData);
    }

    public function testGetLanguage(): void
    {
        $this->languagesService->expects($this->once())
            ->method('getLanguageById')
            ->with(1)
            ->willReturn([
                'message' => 'Language retrieved successfully.',
                'language' => [
                    'LanguageID' => 1,
                    'LanguageCode' => 'ENG',
                    'LanguageName' => 'English'
                ]
            ]);

        $response = $this->controller->getLanguage(1);

        $this->assertEquals(JsonResponse::HTTP_OK, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('status', $responseData);
        $this->assertEquals('success', $responseData['status']);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertArrayHasKey('data', $responseData);
        $this->assertArrayHasKey('language', $responseData['data']);
    }

    public function testGetLanguagesWithNoData(): void
    {
        $this->languagesService->expects($this->once())
            ->method('getAllLanguages')
            ->willReturn([
                'languages' => [],
                'message' => 'No languages found in the database.'
            ]);

        $response = $this->controller->getLanguages();

        $this->assertEquals(JsonResponse::HTTP_OK, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('status', $responseData);
        $this->assertEquals('success', $responseData['status']);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertArrayHasKey('data', $responseData);
        $this->assertArrayHasKey('languages', $responseData['data']);
    }

    public function testLanguageNotFound(): void
    {
        $this->languagesService->method('getLanguageById')
            ->will($this->throwException(new \InvalidArgumentException("Language with ID 10 not found.")));

        $response = $this->controller->getLanguage(10);

        $this->assertEquals(JsonResponse::HTTP_NOT_FOUND, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('status', $responseData);
        $this->assertEquals('error', $responseData['status']);
        $this->assertArrayHasKey('message', $responseData);
    }
}
