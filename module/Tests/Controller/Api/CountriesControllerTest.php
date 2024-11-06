<?php
//0
namespace Module\Tests\Controller\Api;

use Module\Countries\Controller\Api\CountriesController;
use Module\Countries\Service\CountriesService;
use Module\Common\Factory\ResponseFactory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Psr\Log\LoggerInterface;

class CountriesControllerTest extends WebTestCase
{
    private CountriesService $countriesService;
    private ResponseFactory $responseFactory;
    private LoggerInterface $logger;
    private CountriesController $controller;

    protected function setUp(): void
    {
        $this->countriesService = $this->createMock(CountriesService::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->responseFactory = new ResponseFactory($this->logger);

        $this->controller = new CountriesController(
            $this->countriesService,
            $this->logger,
            $this->responseFactory
        );
    }

    public function testGetCountries(): void
    {
        $this->countriesService->method('getAllCountries')
            ->willReturn(['countries' => []]);

        $response = $this->controller->getCountries();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('success', $responseData['status']);
        $this->assertArrayHasKey('data', $responseData);
        $this->assertArrayHasKey('countries', $responseData['data']);
    }

    public function testGetCountry(): void
    {
        $countryData = [
            'country' => [
                'CountryID' => 1,
                'CountryLink' => 'example-country',
            ],
            'translations' => []
        ];

        $this->countriesService->method('getCountryById')
            ->with(1)
            ->willReturn($countryData);

        $response = $this->controller->getCountry(1);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('success', $responseData['status']);
        $this->assertArrayHasKey('data', $responseData);
        $this->assertArrayHasKey('country', $responseData['data']);
    }

    public function testAddCountry(): void
    {
        $countryData = [
            'country' => [
                'CountryID' => 1,
                'CountryLink' => 'example-country',
            ],
            'message' => 'Country added successfully.'
        ];

        $this->countriesService->method('createCountry')
            ->willReturn($countryData);

        $request = new Request([], [], [], [], [], [], json_encode(['CountryLink' => 'example-country']));
        $response = $this->controller->addCountry($request);

        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('success', $responseData['status']);
        $this->assertEquals('Country added successfully.', $responseData['message']);
    }

    public function testAddCountryWithInvalidLink(): void
    {
        $this->countriesService->method('createCountry')
            ->will($this->throwException(new \InvalidArgumentException("Field 'CountryLink' is required.")));

        $request = new Request([], [], [], [], [], [], json_encode(['CountryLink' => '']));
        $response = $this->controller->addCountry($request);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('error', $responseData['status']);
        $this->assertEquals("Field 'CountryLink' is required.", $responseData['message']);
    }

    public function testUpdateCountryLink(): void
    {
        $countryData = [
            'country' => [
                'CountryID' => 1,
                'CountryLink' => 'updated-country-link',
            ],
            'message' => 'Country link updated successfully.'
        ];

        $this->countriesService->method('updateCountryLink')
            ->willReturn($countryData);

        $request = new Request([], [], [], [], [], [], json_encode(['CountryLink' => 'updated-country-link']));
        $response = $this->controller->updateCountryLink(1, $request);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('success', $responseData['status']);
        $this->assertEquals('Country link updated successfully.', $responseData['message']);
    }

    public function testDeleteCountry(): void
    {
        $this->countriesService->method('deleteCountry')
            ->with(1)
            ->willReturn(['message' => "Country with ID 1 and its translations successfully deleted."]);

        $response = $this->controller->deleteCountry(1);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('success', $responseData['status']);
        $this->assertEquals("Country with ID 1 and its translations successfully deleted.", $responseData['message']);
    }

    public function testAddCountryTranslation(): void
    {
        $translationData = [
            'country' => [
                'CountryID' => 1,
                'CountryLink' => 'example-country',
            ],
            'translation' => [
                'TranslationID' => 1,
                'LanguageID' => 2,
                'CountryName' => 'Example Name',
                'CountryDescription' => 'Example Description'
            ],
            'message' => 'Country translation added successfully.'
        ];

        $this->countriesService->method('createCountryTranslation')
            ->willReturn($translationData);

        $request = new Request([], [], [], [], [], [], json_encode(['LanguageID' => 2, 'CountryName' => 'Example Name', 'CountryDescription' => 'Example Description']));
        $response = $this->controller->addCountryTranslation(1, $request);

        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('success', $responseData['status']);
        $this->assertEquals('Country translation added successfully.', $responseData['message']);
    }

    public function testDeleteCountryTranslation(): void
    {
        $this->countriesService->method('deleteCountryTranslation')
            ->with(1, 2)
            ->willReturn(['message' => "Translation with ID 2 successfully deleted for Country ID 1."]);

        $response = $this->controller->deleteCountryTranslation(1, 2);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('success', $responseData['status']);
        $this->assertEquals("Translation with ID 2 successfully deleted for Country ID 1.", $responseData['message']);
    }

    public function testAddCountryWithDuplicateLink(): void
    {
        $this->countriesService->method('createCountry')
            ->will($this->throwException(new \InvalidArgumentException("CountryLink 'example-country' already exists.")));

        $request = new Request([], [], [], [], [], [], json_encode(['CountryLink' => 'example-country']));
        $response = $this->controller->addCountry($request);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('error', $responseData['status']);
        $this->assertEquals("CountryLink 'example-country' already exists.", $responseData['message']);
    }

    public function testDeleteNonExistingCountry(): void
    {
        $this->countriesService->method('deleteCountry')
            ->will($this->throwException(new \InvalidArgumentException("Country with ID 99 not found.")));

        $response = $this->controller->deleteCountry(99);

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('error', $responseData['status']);
        $this->assertEquals("Country with ID 99 not found.", $responseData['message']);
    }
}
