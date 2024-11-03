<?php

namespace Module\Tests\Controller\Api;

use Module\Categories\Controller\Api\CategoriesController;
use Module\Categories\Service\CategoriesService;
use Module\Common\Factory\ResponseFactory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class CategoriesControllerTest extends WebTestCase
{
    private $categoriesService;
    private $responseFactory;
    private $logger;
    private $controller;

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

    //testGetCategories: Проверяет успешное получение списка категорий.
    public function testGetCategories(): void
    {
        $this->categoriesService->method('getAllCategories')
            ->willReturn(['Categories' => []]);

        $response = $this->controller->getCategories();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('Categories', $responseData);
    }

    //2. testGetCategory: Проверяет успешное получение конкретной категории по ID
    public function testGetCategory(): void
    {
        $categoryData = [
            'Category' => [
                'CategoryID' => 1,
                'CategoryLink' => 'example-category',
                'CreatedDate' => '2024-11-02',
            ],
            'Translations' => []
        ];

        $this->categoriesService->method('getCategoryById')
            ->with(1)
            ->willReturn($categoryData);

        $response = $this->controller->getCategory(1);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('Category', $responseData);
    }

    //3.testAddCategory: Проверяет успешное добавление новой категории с корректными данными
    public function testAddCategory(): void
    {
        $categoryData = [
            'Category' => [
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
        $this->assertEquals('Category added successfully.', $responseData['message']);
    }

    //4. testAddCategoryWithInvalidLink: Проверяет, что при попытке добавить категорию с пустым значением CategoryLink
    // возвращается ошибка валидации с правильным сообщением и статусом HTTP_BAD_REQUEST.
    public function testAddCategoryWithInvalidLink(): void
    {
        $this->categoriesService->method('createCategory')
            ->will($this->throwException(new \InvalidArgumentException("Field 'CategoryLink' is required.")));

        $request = new Request([], [], [], [], [], [], json_encode(['CategoryLink' => '']));
        $response = $this->controller->addCategory($request);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals("Field 'CategoryLink' is required.", $responseData['error']);
    }

    //5. testUpdateCategoryLink: Проверяет успешное обновление ссылки категории (CategoryLink)
    public function testUpdateCategoryLink(): void
    {
        $categoryData = [
            'Category' => [
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
        $this->assertEquals('Category link updated successfully.', $responseData['message']);
    }

    //6. testDeleteCategory: Проверяет успешное удаление категории.
    public function testDeleteCategory(): void
    {
        $this->categoriesService->method('deleteCategory')
            ->with(1)
            ->willReturn(['message' => "Category with ID 1 and its translations successfully deleted."]);

        $response = $this->controller->deleteCategory(1);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals("Category with ID 1 and its translations successfully deleted.", $responseData['message']);
    }

    //7. testAddCategoryTranslation: Проверяет успешное добавление перевода для категории.
    public function testAddCategoryTranslation(): void
    {
        $translationData = [
            'Category' => [
                'CategoryID' => 1,
                'CategoryLink' => 'example-category',
            ],
            'Translation' => [
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
        $this->assertEquals('Category translation added successfully.', $responseData['message']);
    }

    //8. testDeleteCategoryTranslation: Проверяет успешное удаление перевода категории.
    public function testDeleteCategoryTranslation(): void
    {
        $this->categoriesService->method('deleteCategoryTranslation')
            ->with(1, 2)
            ->willReturn(['message' => "Translation with ID 2 successfully deleted for Category ID 1."]);

        $response = $this->controller->deleteCategoryTranslation(1, 2);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals("Translation with ID 2 successfully deleted for Category ID 1.", $responseData['message']);
    }

    //9. testAddCategoryWithEmptyLink: Проверяет, что контроллер вернет ошибку,
    // если CategoryLink пустой при добавлении новой категории.
    public function testAddCategoryWithEmptyLink(): void
    {
        $this->categoriesService->method('createCategory')
            ->will($this->throwException(new \InvalidArgumentException("Field 'CategoryLink' is required.")));

        $request = new Request([], [], [], [], [], [], json_encode(['CategoryLink' => '']));
        $response = $this->controller->addCategory($request);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals("Field 'CategoryLink' is required.", $responseData['error']);
    }

    //10. testAddCategoryWithInvalidCharactersInLink: Проверяет, что контроллер возвращает ошибку,
    // если CategoryLink содержит недопустимые символы.
    public function testAddCategoryWithInvalidCharactersInLink(): void
    {
        $this->categoriesService->method('createCategory')
            ->will($this->throwException(new \InvalidArgumentException("Field 'CategoryLink' can contain only letters, numbers, underscores, and hyphens.")));

        $request = new Request([], [], [], [], [], [], json_encode(['CategoryLink' => 'Invalid@Link!']));
        $response = $this->controller->addCategory($request);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals("Field 'CategoryLink' can contain only letters, numbers, underscores, and hyphens.", $responseData['error']);
    }

    //11. testAddCategoryWithDuplicateLink: Проверяет, что при добавлении категории с дублирующим
    // CategoryLink контроллер вернёт ошибку о существующем значении.
    public function testAddCategoryWithDuplicateLink(): void
    {
        $this->categoriesService->method('createCategory')
            ->will($this->throwException(new \InvalidArgumentException("CategoryLink 'example-category' already exists.")));

        $request = new Request([], [], [], [], [], [], json_encode(['CategoryLink' => 'example-category']));
        $response = $this->controller->addCategory($request);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals("CategoryLink 'example-category' already exists.", $responseData['error']);
    }

    //12. testAddCategoryTranslationWithEmptyName:
    // Проверяет, что при добавлении перевода с пустым CategoryName контроллер возвращает ошибку.
    public function testAddCategoryTranslationWithEmptyName(): void
    {
        $this->categoriesService->method('createCategoryTranslation')
            ->will($this->throwException(new \InvalidArgumentException("Field 'CategoryName' is required.")));

        $request = new Request([], [], [], [], [], [], json_encode([
            'LanguageID' => 1,
            'CategoryName' => '',
            'CategoryDescription' => 'Description'
        ]));

        $response = $this->controller->addCategoryTranslation(1, $request);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals("Field 'CategoryName' is required.", $responseData['error']);
    }

    //13. testAddCategoryTranslationWithLongName:
    // Проверяет, что контроллер вернёт ошибку при слишком длинном значении CategoryName.
    public function testAddCategoryTranslationWithLongName(): void
    {
        $this->categoriesService->method('createCategoryTranslation')
            ->will($this->throwException(new \InvalidArgumentException("Field 'CategoryName' can contain only up to 20 characters.")));

        $request = new Request([], [], [], [], [], [], json_encode([
            'LanguageID' => 1,
            'CategoryName' => 'ThisNameIsWayTooLongForValidation',
            'CategoryDescription' => 'Description'
        ]));

        $response = $this->controller->addCategoryTranslation(1, $request);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals("Field 'CategoryName' can contain only up to 20 characters.", $responseData['error']);
    }

    //14. testAddCategoryTranslationWithInvalidCharactersInName:
    // Проверяет, что контроллер возвращает ошибку, если CategoryName содержит недопустимые символы.
    public function testAddCategoryTranslationWithInvalidCharactersInName(): void
    {
        $this->categoriesService->method('createCategoryTranslation')
            ->will($this->throwException(new \InvalidArgumentException("Field 'CategoryName' can contain only letters, numbers, underscores, and hyphens.")));

        $request = new Request([], [], [], [], [], [], json_encode([
            'LanguageID' => 1,
            'CategoryName' => 'Invalid@Name!',
            'CategoryDescription' => 'Description'
        ]));

        $response = $this->controller->addCategoryTranslation(1, $request);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals("Field 'CategoryName' can contain only letters, numbers, underscores, and hyphens.", $responseData['error']);
    }

    //15. testUpdateCategoryWithInvalidCategoryLink: Проверяет, что при попытке обновить категорию с невалидным
    // значением CategoryLink контроллер возвращает ошибку валидации.
    public function testUpdateCategoryWithInvalidCategoryLink(): void
    {
        $this->categoriesService->method('updateCategoryLink')
            ->will($this->throwException(new \InvalidArgumentException("Field 'CategoryLink' contains invalid characters.")));

        $request = new Request([], [], [], [], [], [], json_encode(['CategoryLink' => 'InvalidLink!']));
        $response = $this->controller->updateCategoryLink(1, $request);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals("Field 'CategoryLink' contains invalid characters.", $responseData['error']);
    }

    //16. testDeleteNonExistingCategory: Проверяет, что при удалении несуществующей категории
    // контроллер возвращает сообщение об ошибке с правильным статусом HTTP_NOT_FOUND
    public function testDeleteNonExistingCategory(): void
    {
        $this->categoriesService->method('deleteCategory')
            ->will($this->throwException(new \InvalidArgumentException("Category with ID 99 not found.")));

        $response = $this->controller->deleteCategory(99);

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals("Category with ID 99 not found.", $responseData['error']);
    }
}
