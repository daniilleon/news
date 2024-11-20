<?php

namespace ContainerKWUSDw5;

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;

/**
 * @internal This class has been auto-generated by the Symfony Dependency Injection Component.
 */
class getEmployeesJobTitleControllerService extends Module_KernelDevDebugContainer
{
    /**
     * Gets the public 'Module\Employees\EmployeesJobTitle\Controller\Api\EmployeesJobTitleController' shared autowired service.
     *
     * @return \Module\Employees\EmployeesJobTitle\Controller\Api\EmployeesJobTitleController
     */
    public static function do($container, $lazyLoad = true)
    {
        include_once \dirname(__DIR__, 4).''.\DIRECTORY_SEPARATOR.'module'.\DIRECTORY_SEPARATOR.'Employees'.\DIRECTORY_SEPARATOR.'EmployeesJobTitle'.\DIRECTORY_SEPARATOR.'Controller'.\DIRECTORY_SEPARATOR.'Api'.\DIRECTORY_SEPARATOR.'EmployeesJobTitleController.php';
        include_once \dirname(__DIR__, 4).''.\DIRECTORY_SEPARATOR.'module'.\DIRECTORY_SEPARATOR.'Employees'.\DIRECTORY_SEPARATOR.'EmployeesJobTitle'.\DIRECTORY_SEPARATOR.'Service'.\DIRECTORY_SEPARATOR.'EmployeesJobTitleService.php';

        $a = ($container->privates['monolog.logger'] ?? $container->load('getMonolog_LoggerService'));

        return $container->services['Module\\Employees\\EmployeesJobTitle\\Controller\\Api\\EmployeesJobTitleController'] = new \Module\Employees\EmployeesJobTitle\Controller\Api\EmployeesJobTitleController(new \Module\Employees\EmployeesJobTitle\Service\EmployeesJobTitleService(($container->privates['Module\\Employees\\EmployeesJobTitle\\Repository\\EmployeesJobTitleRepository'] ?? $container->load('getEmployeesJobTitleRepositoryService')), ($container->privates['Module\\Employees\\EmployeesJobTitle\\Repository\\EmployeeJobTitleTranslationsRepository'] ?? $container->load('getEmployeeJobTitleTranslationsRepositoryService')), ($container->privates['Module\\Common\\Proxy\\Core\\LanguagesProxyService'] ?? $container->load('getLanguagesProxyServiceService')), ($container->privates['Module\\Employees\\EmployeesJobTitle\\Service\\EmployeesJobTitleValidationService'] ?? $container->load('getEmployeesJobTitleValidationServiceService')), ($container->privates['Module\\Common\\Service\\ImageService'] ?? $container->load('getImageServiceService')), ($container->privates['Module\\Common\\Helpers\\FieldUpdateHelper'] ?? $container->load('getFieldUpdateHelperService')), $a), $a, ($container->privates['Module\\Common\\Factory\\ResponseFactory'] ?? $container->load('getResponseFactoryService')));
    }
}
