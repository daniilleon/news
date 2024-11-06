<?php
//0
namespace Module\Tests\Controller\Api;

use Module\Categories\Controller\Api\CategoriesController;
use Module\Categories\Service\CategoriesService;
use Module\Common\Factory\ResponseFactory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Psr\Log\LoggerInterface;

class CategoriesControllerTest extends WebTestCase
{
    private CategoriesService $categoriesService;
    private ResponseFactory $responseFactory;
    private LoggerInterface $logger;
    private CategoriesController $controller;

    protected function setUp(): void
    {
        $this->categoriesService = $this->createMock(CategoriesService::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->responseFactory = new ResponseFactory($this->logger);

        $this->controller = new CategoriesController(
            $this->categoriesService,
            $this->logger,
            $this->responseFactory
        );
    }

    public function testGetCategories(): void
    {
        $this->categoriesService->method('getAllCategories')
            ->willReturn(['categories' => []]);

        $response = $this->controller->getCategories();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('success', $responseData['status']);
        $this->assertArrayHasKey('data', $responseData);
        $this->assertArrayHasKey('categories', $responseData['data']);
    }

    public function testGetCategory(): void
    {
        $categoryData = [
            'category' => [
                'CategoryID' => 1,
                'CategoryLink' => 'example-category',
            ],
            'translations' => []
        ];

        $this->categoriesService->method('getCategoryById')
            ->with(1)
            ->willReturn($categoryData);

        $response = $this->controller->getCategory(1);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('success', $responseData['status']);
        $this->assertArrayHasKey('data', $responseData);
        $this->assertArrayHasKey('category', $responseData['data']);
    }

    public function testAddCategory(): void
    {
        $categoryData = [
            'category' => [
                'CategoryID' => 1,
                'CategoryLink' => 'example-category',
            ],
            'message' => 'Category added successfully.'
        ];

        $this->categoriesService->method('createCategory')
            ->willReturn($categoryData);

        $request = new Request([], [], [], [], [], [], json_encode(['CategoryLink' => 'example-category']));
        $response = $this->controller->addCategory($request);

        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('success', $responseData['status']);
        $this->assertEquals('Category added successfully.', $responseData['message']);
    }

    public function testAddCategoryWithInvalidLink(): void
    {
        $this->categoriesService->method('createCategory')
            ->will($this->throwException(new \InvalidArgumentException("Field 'CategoryLink' is required.")));

        $request = new Request([], [], [], [], [], [], json_encode(['CategoryLink' => '']));
        $response = $this->controller->addCategory($request);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('error', $responseData['status']);
        $this->assertEquals("Field 'CategoryLink' is required.", $responseData['message']);
    }

    public function testUpdateCategoryLink(): void
    {
        $categoryData = [
            'category' => [
                'CategoryID' => 1,
                'CategoryLink' => 'updated-category-link',
            ],
            'message' => 'Category link updated successfully.'
        ];

        $this->categoriesService->method('updateCategoryLink')
            ->willReturn($categoryData);

        $request = new Request([], [], [], [], [], [], json_encode(['CategoryLink' => 'updated-category-link']));
        $response = $this->controller->updateCategoryLink(1, $request);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('success', $responseData['status']);
        $this->assertEquals('Category link updated successfully.', $responseData['message']);
    }

    public function testDeleteCategory(): void
    {
        $this->categoriesService->method('deleteCategory')
            ->with(1)
            ->willReturn(['message' => "Category with ID 1 and its translations successfully deleted."]);

        $response = $this->controller->deleteCategory(1);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('success', $responseData['status']);
        $this->assertEquals("Category with ID 1 and its translations successfully deleted.", $responseData['message']);
    }

    public function testAddCategoryTranslation(): void
    {
        $translationData = [
            'category' => [
                'CategoryID' => 1,
                'CategoryLink' => 'example-category',
            ],
            'translation' => [
                'TranslationID' => 1,
                'LanguageID' => 2,
                'CategoryName' => 'Example Name',
                'CategoryDescription' => 'Example Description'
            ],
            'message' => 'Category translation added successfully.'
        ];

        $this->categoriesService->method('createCategoryTranslation')
            ->willReturn($translationData);

        $request = new Request([], [], [], [], [], [], json_encode(['LanguageID' => 2, 'CategoryName' => 'Example Name', 'CategoryDescription' => 'Example Description']));
        $response = $this->controller->addCategoryTranslation(1, $request);

        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('success', $responseData['status']);
        $this->assertEquals('Category translation added successfully.', $responseData['message']);
    }

    public function testDeleteCategoryTranslation(): void
    {
        $this->categoriesService->method('deleteCategoryTranslation')
            ->with(1, 2)
            ->willReturn(['message' => "Translation with ID 2 successfully deleted for Category ID 1."]);

        $response = $this->controller->deleteCategoryTranslation(1, 2);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('success', $responseData['status']);
        $this->assertEquals("Translation with ID 2 successfully deleted for Category ID 1.", $responseData['message']);
    }

    public function testAddCategoryWithDuplicateLink(): void
    {
        $this->categoriesService->method('createCategory')
            ->will($this->throwException(new \InvalidArgumentException("CategoryLink 'example-category' already exists.")));

        $request = new Request([], [], [], [], [], [], json_encode(['CategoryLink' => 'example-category']));
        $response = $this->controller->addCategory($request);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('error', $responseData['status']);
        $this->assertEquals("CategoryLink 'example-category' already exists.", $responseData['message']);
    }

    public function testDeleteNonExistingCategory(): void
    {
        $this->categoriesService->method('deleteCategory')
            ->will($this->throwException(new \InvalidArgumentException("Category with ID 99 not found.")));

        $response = $this->controller->deleteCategory(99);

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('error', $responseData['status']);
        $this->assertEquals("Category with ID 99 not found.", $responseData['message']);
    }
}
