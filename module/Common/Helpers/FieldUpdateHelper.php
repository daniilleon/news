<?php

namespace Module\Common\Helpers;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class FieldUpdateHelper
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
     * Универсальный метод для проверки и обновления поля объекта
     *
     * @param object $entity Объект, поле которого нужно обновить
     * @param array $data Данные, содержащие обновляемые значения
     * @param string $field Имя поля, которое нужно проверить и обновить
     * @param callable|null $validationCallback (необязательно) Валидационная функция, которая будет вызываться перед обновлением
     */
    public static function updateFieldIfPresent(
        object $entity,
        array $data,
        string $field,
        callable $validationCallback = null
    ): void {
        $getter = 'get' . ucfirst($field);
        $setter = 'set' . ucfirst($field);

        // Проверка на существование методов getter и setter в объекте
        if (!method_exists($entity, $getter) || !method_exists($entity, $setter)) {
            throw new \InvalidArgumentException("Field '$field' does not exist on entity " . get_class($entity));
        }

        // Получаем текущее значение поля
        $currentValue = $entity->$getter();

        // Проверяем наличие нового значения для поля в данных и выполняем валидацию, если указано
        $newValue = $data[$field] ?? $currentValue;
        if ($validationCallback && isset($data[$field])) {
            $validationCallback($newValue);
        }

        // Обновляем поле, если новое значение отличается от текущего
        if (isset($data[$field]) && $currentValue !== $data[$field]) {
            $entity->$setter($data[$field]);
        }
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
    public function validateAndFilterFields(object $entity, array $data): array
    {
        $allowedFields = [];
        $filteredData = [];

        try {
            // Получаем список методов сущности
            $reflectionClass = new \ReflectionClass($entity);
            foreach ($reflectionClass->getMethods() as $method) {
                if (strpos($method->getName(), 'set') === 0) {
                    // Извлекаем имя поля в camelCase
                    $field = lcfirst(substr($method->getName(), 3));
                    $allowedFields[] = $field;
                }
            }

            // Проверка, что данные не пустые
            if (empty($data)) {
                throw new \InvalidArgumentException("No data provided. Please provide at least one field to update.");
            }

            // Проходим по входным данным и проверяем, что они разрешены
            foreach ($data as $key => $value) {
                $camelCaseKey = lcfirst(str_replace('_', '', ucwords($key, '_')));
                if (in_array($camelCaseKey, $allowedFields, true)) {
                    $filteredData[$camelCaseKey] = $value;
                } else {
                    throw new \InvalidArgumentException("Invalid field provided: $key");
                }
            }

            // Проверка, что есть хотя бы одно допустимое поле для обновления
            if (empty($filteredData)) {
                throw new \InvalidArgumentException("No valid fields provided. Allowed fields are: " . implode(', ', $allowedFields));
            }
        } catch (\InvalidArgumentException $e) {
            $this->logger->error("Field validation failed: " . $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error("An error occurred while validating fields: " . $e->getMessage());
            throw new \RuntimeException("Unable to validate fields at this time.", 0, $e);
        }

        return $filteredData;
    }
}
