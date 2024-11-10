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
     * Метод updateFieldIfPresent — это универсальный способ проверить и обновить значение конкретного поля объекта,
     * если оно передано в массиве данных $data, с возможностью валидации перед обновлением.
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
     * Метод validateAndFilterFields отвечает за проверку и фильтрацию входных данных на основе
     * разрешенных полей для конкретной сущности. Его основное назначение:
     *
     * Валидация полей: Проверить, что только разрешенные поля включены в данные,
     * которые поступают для обновления или создания сущности.
     *
     * Фильтрация данных: Создать массив только с теми полями,
     * которые соответствуют разрешенным и существующим в сущности полям.
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
                    $this->logger->info("Allowed field added: $field");
                }
            }

            // Проверка, что данные не пустые
            if (empty($data)) {
                throw new \InvalidArgumentException("No data provided. Please provide at least one field to update.");
            }

            // Проходим по входным данным и проверяем, что они разрешены
            foreach ($data as $key => $value) {
                $camelCaseKey = lcfirst(str_replace('_', '', ucwords($key, '_')));
                $this->logger->info("Processing field: $key as $camelCaseKey");
                $this->logger->info("Validating field: $key with value: $value");
                if (in_array($camelCaseKey, $allowedFields, true)) {
                    $filteredData[$camelCaseKey] = $value;
                } else {
                    $this->logger->warning("Field $key is not recognized as a valid field for update.");
                    throw new \InvalidArgumentException("Invalid field provided: $key");
                }
            }
            $this->logger->info("Filtered data after validation:", $filteredData);

            // Проверка, что есть хотя бы одно допустимое поле для обновления
            if (empty($filteredData)) {
                $this->logger->error("No valid fields provided.");
                throw new \InvalidArgumentException("No valid fields provided. Allowed fields are: " . implode(', ', $allowedFields));
            }
            $this->logger->info("Filtered fields: ", $filteredData);


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
