<?php

namespace Module\Common\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Psr\Log\LoggerInterface;

class ImageService
{
    private LoggerInterface $logger;
    private ValidatorInterface $validator; // Добавляем валидатор
    public function __construct(
        ValidatorInterface $validator,
        LoggerInterface $logger,
    ) {
        $this->validator = $validator;
        $this->logger = $logger;
    }

    /**
     * Загрузка изображения с валидацией и сохранением в заданный каталог
     *
     * @param UploadedFile $file Файл изображения
     * @param int $entityId ID сущности, например, категории, новости или города
     * @param string $baseDir Базовая директория для хранения, например 'categories', 'news', 'countries'
     * @return string Относительный путь к сохраненному изображению
     * @throws \InvalidArgumentException Если файл не прошел валидацию
     */
    public function uploadOgImage(?UploadedFile $file, int $entityId, string $baseDir, ?string $oldImagePath = null): string
    {
        try {
            // Проверка на наличие файла
            if (!$file) {
                throw new \InvalidArgumentException("Image file 'og_image' is required.");
            }

            // Валидация файла
            $constraints = new Assert\Collection([
                'file' => [
                    new Assert\File([
                        'maxSize' => '2M',
                        'mimeTypes' => ['image/jpeg', 'image/png'],
                        'mimeTypesMessage' => 'Allowed file types are JPG and PNG.',
                    ]),
                    new Assert\Image([
                        'minWidth' => 1200,
                        'maxWidth' => 1200,
                        'minHeight' => 630,
                        'maxHeight' => 630,
                        'allowLandscape' => true,
                        'allowPortrait' => false,
                        'allowSquare' => false,
                        'minWidthMessage' => 'The width of the image must be exactly 1200 pixels.',
                        'maxWidthMessage' => 'The width of the image must be exactly 1200 pixels.',
                        'minHeightMessage' => 'The height of the image must be exactly 630 pixels.',
                        'maxHeightMessage' => 'The height of the image must be exactly 630 pixels.',
                    ])
                ]
            ]);

            $violations = $this->validator->validate(['file' => $file], $constraints);

            if (count($violations) > 0) {
                $errorMessage = [];
                foreach ($violations as $violation) {
                    $errorMessage[] = $violation->getMessage();
                }
                throw new \InvalidArgumentException(implode("\n", $errorMessage));
            }

            // Определяем пути для сохранения изображения
            $relativeUploadDir = 'images/' . trim($baseDir, '/') . '/' . $entityId;  // Относительный путь без 'public/'
            $absoluteUploadDir = __DIR__ . '/../../../public/' . $relativeUploadDir; // Абсолютный путь с 'public/'

            if (!is_dir($absoluteUploadDir)) {
                mkdir($absoluteUploadDir, 0777, true); // Создаем директорию, если она не существует
            }

            // Устанавливаем уникальное имя для файла
            $fileName = uniqid() . '.' . $file->getClientOriginalExtension();
            $absoluteFilePath = $absoluteUploadDir . '/' . $fileName; // Полный путь для сохранения файла

            // Перемещаем файл в указанную директорию
            $file->move($absoluteUploadDir, $fileName);

            // Удаление старого изображения, если оно существует
            if ($oldImagePath) {
                $oldFilePath = __DIR__ . '/../../../public' . $oldImagePath;
                $this->logger->info("Checking for old image file at: $oldFilePath before deletion");

                if (file_exists($oldFilePath)) {
                    $this->logger->info("Old image file found. Attempting to delete: $oldFilePath");
                    if (unlink($oldFilePath)) {
                        $this->logger->info("Old image file deleted successfully: $oldFilePath");
                    } else {
                        $this->logger->error("Failed to delete old image file: $oldFilePath");
                    }
                } else {
                    $this->logger->warning("Old image file not found, double-checking path: $oldFilePath");
                }
            }

            // Возвращаем относительный путь для сохранения в базе данных
            return '/' . $relativeUploadDir . '/' . $fileName;

        } catch (\InvalidArgumentException $e) {
            $this->logger->error("Image validation failed: " . $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error("An error occurred while uploading the image: " . $e->getMessage());
            throw new \RuntimeException("Unable to upload image at this time.", 0, $e);
        }
    }



}