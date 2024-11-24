<?php

function generateFile(string $templatePath, array $replacements, string $outputPath): void
{
    $template = file_get_contents($templatePath);

    if (!$template) {
        throw new RuntimeException("Template $templatePath not found.");
    }

    // Заменяем переменные
    $content = str_replace(array_keys($replacements), array_values($replacements), $template);

    // Создаём директорию, если её нет
    $directory = dirname($outputPath);
    if (!is_dir($directory)) {
        mkdir($directory, 0777, true);
    }

    // Сохраняем файл
    file_put_contents($outputPath, $content);

    echo "Generated: $outputPath\n";
}

// Настройки модуля
$module = 'Papka';

$entity = 'MissionsStatements';
$entity_one = 'MissionStatement';
$entity_lower = 'missionStatement';
$entity_dir = "Shared";
$entity_code_link = "Code";

// Заменяемые переменные
$replacements = [
    '{{MODULE_NAMESPACE}}' => $entity, //$module
    '{{MODULE_NAMESPACE_LOWER}}' => strtolower($module),
    '{{ENTITY_NAME}}' => $entity,
    '{{ENTITY_NAME_ONE}}' => $entity_one,
    '{{ENTITY_NAME_LOWER}}' => $entity_lower,
    '{{ENTITY_NAME_LOWER_ONLY}}' => strtolower($entity_lower),
    '{{ENTITY_DIR}}' => $entity_dir,
    '{{ENTITY_CODE_LINK}}' => $entity_code_link,
    '{{ENTITY_CODE_LINK_LOWER}}' => strtolower($entity_code_link),
];

// Пути шаблонов
$entityTemplate = __DIR__ .                 '/Entity/EntityTemplate.php';
$translationsTemplate = __DIR__ .           '/Entity/EntityTranslationsTemplate.php';
$repositoryTemplate = __DIR__ .             '/Repository/EntityRepositoryTemplate.php';
$translationsRepositoryTemplate = __DIR__ . '/Repository/EntityTranslationsRepositoryTemplate.php';
$serviceValidationTemplate = __DIR__ .      '/Service/EntityValidationServiceTemplate.php';
$serviceTemplate = __DIR__ .                '/Service/EntityServiceTemplate.php';
$controllerTemplate = __DIR__ .             '/Controller/EntityControllerTemplate.php';
$proxyTemplate = __DIR__ .                  '/Proxy/EntityProxyService.php';

// Пути для сохранения файлов
$outputEntityPath = __DIR__ . "/../../module/$entity_dir/$entity/Entity/$entity.php";
$outputTranslationsPath = __DIR__ . "/../../module/$entity_dir/$entity/Entity/{$entity_one}Translations.php";
$outputRepositoryPath = __DIR__ . "/../../module/$entity_dir/$entity/Repository/{$entity}Repository.php";
$outputTranslationsRepositoryPath = __DIR__ . "/../../module/$entity_dir/$entity/Repository/{$entity_one}TranslationsRepository.php";
$outputServiceValidationPath = __DIR__ . "/../../module/$entity_dir/$entity/Service/{$entity}ValidationService.php";
$outputServicePath = __DIR__ . "/../../module/$entity_dir/$entity/Service/{$entity}Service.php";
$outputControllerPath = __DIR__ . "/../../module/$entity_dir/$entity/Controller/Api/{$entity}Controller.php";
$outputProxyPath = __DIR__ . "/../../module/Common/Proxy/$entity_dir/{$entity}ProxyService.php";

// Генерируем файлы
generateFile($entityTemplate, $replacements, $outputEntityPath);
generateFile($translationsTemplate, $replacements, $outputTranslationsPath);
generateFile($repositoryTemplate, $replacements, $outputRepositoryPath);
generateFile($translationsRepositoryTemplate, $replacements, $outputTranslationsRepositoryPath);
generateFile($serviceValidationTemplate, $replacements, $outputServiceValidationPath);
generateFile($serviceTemplate, $replacements, $outputServicePath);
generateFile($controllerTemplate, $replacements, $outputControllerPath);
generateFile($proxyTemplate, $replacements, $outputProxyPath);