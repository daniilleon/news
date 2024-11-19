<?php

namespace Module\Countries\Controller\Api;

use Module\Countries\Service\CountriesService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Module\Common\Factory\ResponseFactory;

// Основной контроллер API для работы с категориями.
#[Route('/api/countries')]
class CountriesController
{
    private CountriesService $countriesService;
    private LoggerInterface $logger;
    private ResponseFactory $responseFactory;

    public function __construct(
        CountriesService $countriesService,
        LoggerInterface $logger,
        ResponseFactory $responseFactory
    ) {
        $this->countriesService = $countriesService;
        $this->logger = $logger;
        $this->responseFactory = $responseFactory;
    }

    // Получение списка всех Стран.
    #[Route('/', name: 'api_get_countries', methods: ['GET'])]
    public function getCountries(): JsonResponse
    {
        try {
            $this->logger->info("Executing getCountries method.");
            $countriesData = $this->countriesService->getAllCountries();
            return $this->responseFactory->createSuccessResponse($countriesData);
        } catch (\Exception $e) {
            $this->logger->error("Failed to fetch countries: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to fetch countries', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Получение данных Страны по ее ID.
    #[Route('/{countryId}', name: 'api_get_country', methods: ['GET'])]
    public function getCountry(int $countryId): JsonResponse
    {
        try {
            $this->logger->info("Executing getCountry method.");
            $countryData = $this->countriesService->getCountryById($countryId);
            return $this->responseFactory->createSuccessResponse($countryData);
        } catch (\InvalidArgumentException $e) {
            $this->logger->warning($e->getMessage());
            return $this->responseFactory->createNotFoundResponse($e->getMessage());
        } catch (\Exception $e) {
            $this->logger->error("Failed to fetch country with ID $countryId: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to fetch country', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Создание новой Страны.
    #[Route('/add', name: 'api_add_country', methods: ['POST'])]
    public function addCountry(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $responseData = $this->countriesService->createCountry($data);
            return $this->responseFactory->createCreatedResponse($responseData);
        } catch (\InvalidArgumentException $e) {
            $this->logger->error("InvalidArgumentException caught in controller: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse($e->getMessage(), JsonResponse::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            $this->logger->error("Failed to add country: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to add country', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{countryId}/upload-image', name: 'api_update_country_image', methods: ['POST'])]
    public function updateCountryImage(int $countryId, Request $request): JsonResponse
    {
        try {
            // Получаем файл og_image из запроса и передаем его в сервис без проверки
            $file = $request->files->get('og_image');
            // Вызываем метод сервиса для обновления изображения
            $responseData = $this->countriesService->updateCountryImage($countryId, $file);
            return $this->responseFactory->createSuccessResponse($responseData);
        } catch (\InvalidArgumentException $e) {
            $this->logger->error("InvalidArgumentException caught in controller: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse($e->getMessage(), JsonResponse::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            $this->logger->error("Failed to update country image: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to update country image', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    // Добавление перевода для Страны.
    #[Route('/{countryId}/add-translation', name: 'api_add_country_translation', methods: ['POST'])]
    public function addCountryTranslation(int $countryId, Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $responseData = $this->countriesService->createCountryTranslation($countryId, $data);
            return $this->responseFactory->createCreatedResponse($responseData);
        } catch (\InvalidArgumentException $e) {
            return $this->responseFactory->createErrorResponse($e->getMessage(), JsonResponse::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            $this->logger->error("Failed to add translation for country ID $countryId: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to add country translation', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Обновление только основной Страны (ссылки)
    #[Route('/{countryId}/update', name: 'api_update_country', methods: ['PUT'])]
    public function updateCountryLink(int $countryId, Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $responseData = $this->countriesService->updateCountryLink($countryId, $data);
            return $this->responseFactory->createSuccessResponse($responseData);
        } catch (\InvalidArgumentException $e) {
            return $this->responseFactory->createErrorResponse($e->getMessage(), JsonResponse::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            $this->logger->error("Failed to update country link for ID $countryId: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to update country link', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Обновление перевода Страны для указанного языка
    #[Route('/{countryId}/update-translation/{translationId}', name: 'api_update_country_translation', methods: ['PUT'])]
    public function updateCountryTranslation(int $countryId, int $translationId, Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $responseData = $this->countriesService->updateCountryTranslation($countryId, $translationId, $data);
            return $this->responseFactory->createSuccessResponse($responseData);
        } catch (\InvalidArgumentException $e) {
            return $this->responseFactory->createErrorResponse($e->getMessage(), JsonResponse::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            $this->logger->error("Failed to update translation for country ID $countryId and language ID $translationId: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to update country translation', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Удаление конкретного перевода Страны по его ID.
    #[Route('/{countryId}/delete-translation/{translationId}', name: 'api_delete_country_translation', methods: ['DELETE'])]
    public function deleteCountryTranslation(int $countryId, int $translationId): JsonResponse
    {
        try {
            $result = $this->countriesService->deleteCountryTranslation($countryId, $translationId);
            return $this->responseFactory->createSuccessResponse($result);
        } catch (\InvalidArgumentException $e) {
            return $this->responseFactory->createNotFoundResponse($e->getMessage());
        } catch (\Exception $e) {
            $this->logger->error("Failed to delete translation with ID $translationId for country ID $countryId: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to delete country translation', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Удаление Страны по ее ID.
    #[Route('/{countryId}/delete', name: 'api_delete_country', methods: ['DELETE'])]
    public function deleteCountry(int $countryId): JsonResponse
    {
        try {
            $result = $this->countriesService->deleteCountry($countryId);
            return $this->responseFactory->createSuccessResponse($result);
        } catch (\InvalidArgumentException $e) {
            return $this->responseFactory->createNotFoundResponse($e->getMessage());
        } catch (\Exception $e) {
            $this->logger->error("Failed to delete country with ID $countryId: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to delete country', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    //Метод заполнения демо данными
    #[Route('/seed', name: 'api_seed_countries_and_translations', methods: ['POST'])]
    public function seedCountriesAndTranslations(): JsonResponse
    {
        try {
            $result = $this->countriesService->seedCountriesAndTranslations();
            return $this->responseFactory->createCreatedResponse($result);
        } catch (\Exception $e) {
            $this->logger->error("Failed to seed countries and translations: " . $e->getMessage());
            return $this->responseFactory->createErrorResponse('Unable to seed countries and translations', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


}
