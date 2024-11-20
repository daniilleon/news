<?php

namespace ContainerKWUSDw5;

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;

/**
 * @internal This class has been auto-generated by the Symfony Dependency Injection Component.
 */
class getEducationLevelsControllerService extends Module_KernelDevDebugContainer
{
    /**
     * Gets the public 'Module\Persons\EducationLevels\Controller\Api\EducationLevelsController' shared autowired service.
     *
     * @return \Module\Persons\EducationLevels\Controller\Api\EducationLevelsController
     */
    public static function do($container, $lazyLoad = true)
    {
        include_once \dirname(__DIR__, 4).''.\DIRECTORY_SEPARATOR.'module'.\DIRECTORY_SEPARATOR.'Persons'.\DIRECTORY_SEPARATOR.'EducationLevels'.\DIRECTORY_SEPARATOR.'Controller'.\DIRECTORY_SEPARATOR.'Api'.\DIRECTORY_SEPARATOR.'EducationLevelsController.php';
        include_once \dirname(__DIR__, 4).''.\DIRECTORY_SEPARATOR.'module'.\DIRECTORY_SEPARATOR.'Persons'.\DIRECTORY_SEPARATOR.'EducationLevels'.\DIRECTORY_SEPARATOR.'Service'.\DIRECTORY_SEPARATOR.'EducationLevelsService.php';
        include_once \dirname(__DIR__, 4).''.\DIRECTORY_SEPARATOR.'module'.\DIRECTORY_SEPARATOR.'Persons'.\DIRECTORY_SEPARATOR.'EducationLevels'.\DIRECTORY_SEPARATOR.'Service'.\DIRECTORY_SEPARATOR.'EducationLevelsValidationService.php';

        $a = ($container->privates['Module\\Persons\\EducationLevels\\Repository\\EducationLevelsRepository'] ?? $container->load('getEducationLevelsRepositoryService'));
        $b = ($container->privates['Module\\Persons\\EducationLevels\\Repository\\EducationLevelTranslationsRepository'] ?? $container->load('getEducationLevelTranslationsRepositoryService'));
        $c = ($container->privates['Module\\Common\\Proxy\\Core\\LanguagesProxyService'] ?? $container->load('getLanguagesProxyServiceService'));
        $d = ($container->privates['monolog.logger'] ?? $container->load('getMonolog_LoggerService'));

        return $container->services['Module\\Persons\\EducationLevels\\Controller\\Api\\EducationLevelsController'] = new \Module\Persons\EducationLevels\Controller\Api\EducationLevelsController(new \Module\Persons\EducationLevels\Service\EducationLevelsService($a, $b, $c, new \Module\Persons\EducationLevels\Service\EducationLevelsValidationService($a, $b, $c, $d), ($container->privates['Module\\Common\\Service\\ImageService'] ?? $container->load('getImageServiceService')), ($container->privates['Module\\Common\\Helpers\\FieldUpdateHelper'] ?? $container->load('getFieldUpdateHelperService')), $d), $d, ($container->privates['Module\\Common\\Factory\\ResponseFactory'] ?? $container->load('getResponseFactoryService')));
    }
}