<?php

namespace ContainerKWUSDw5;

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;

/**
 * @internal This class has been auto-generated by the Symfony Dependency Injection Component.
 */
class getEmployeesControllerService extends Module_KernelDevDebugContainer
{
    /**
     * Gets the public 'Module\Employees\Employees\Controller\Api\EmployeesController' shared autowired service.
     *
     * @return \Module\Employees\Employees\Controller\Api\EmployeesController
     */
    public static function do($container, $lazyLoad = true)
    {
        include_once \dirname(__DIR__, 4).''.\DIRECTORY_SEPARATOR.'module'.\DIRECTORY_SEPARATOR.'Employees'.\DIRECTORY_SEPARATOR.'Employees'.\DIRECTORY_SEPARATOR.'Controller'.\DIRECTORY_SEPARATOR.'Api'.\DIRECTORY_SEPARATOR.'EmployeesController.php';
        include_once \dirname(__DIR__, 4).''.\DIRECTORY_SEPARATOR.'module'.\DIRECTORY_SEPARATOR.'Employees'.\DIRECTORY_SEPARATOR.'Employees'.\DIRECTORY_SEPARATOR.'Service'.\DIRECTORY_SEPARATOR.'EmployeesService.php';
        include_once \dirname(__DIR__, 4).''.\DIRECTORY_SEPARATOR.'module'.\DIRECTORY_SEPARATOR.'Common'.\DIRECTORY_SEPARATOR.'Proxy'.\DIRECTORY_SEPARATOR.'Core'.\DIRECTORY_SEPARATOR.'CategoriesProxyService.php';
        include_once \dirname(__DIR__, 4).''.\DIRECTORY_SEPARATOR.'module'.\DIRECTORY_SEPARATOR.'Employees'.\DIRECTORY_SEPARATOR.'Employees'.\DIRECTORY_SEPARATOR.'Service'.\DIRECTORY_SEPARATOR.'EmployeesValidationService.php';

        $a = ($container->privates['Module\\Employees\\Employees\\Repository\\EmployeesRepository'] ?? $container->load('getEmployeesRepositoryService'));
        $b = ($container->privates['Module\\Common\\Proxy\\Core\\LanguagesProxyService'] ?? $container->load('getLanguagesProxyServiceService'));
        $c = ($container->privates['monolog.logger'] ?? $container->load('getMonolog_LoggerService'));

        $d = new \Module\Common\Proxy\Core\CategoriesProxyService(($container->privates['Module\\Core\\Categories\\Repository\\CategoriesRepository'] ?? $container->load('getCategoriesRepositoryService')), ($container->privates['Module\\Core\\Categories\\Repository\\CategoryTranslationsRepository'] ?? $container->load('getCategoryTranslationsRepositoryService')), ($container->privates['Module\\Core\\Categories\\Service\\CategoriesValidationService'] ?? $container->load('getCategoriesValidationServiceService')), $c);
        $e = ($container->privates['Module\\Employees\\EmployeesJobTitle\\Service\\EmployeesJobTitleValidationService'] ?? $container->load('getEmployeesJobTitleValidationServiceService'));

        return $container->services['Module\\Employees\\Employees\\Controller\\Api\\EmployeesController'] = new \Module\Employees\Employees\Controller\Api\EmployeesController(new \Module\Employees\Employees\Service\EmployeesService($a, ($container->privates['Module\\Employees\\EmployeesJobTitle\\Repository\\EmployeesJobTitleRepository'] ?? $container->load('getEmployeesJobTitleRepositoryService')), $b, $d, new \Module\Employees\Employees\Service\EmployeesValidationService($a, $b, $d, $e, $c), $e, ($container->privates['Module\\Common\\Helpers\\FieldUpdateHelper'] ?? $container->load('getFieldUpdateHelperService')), $c), $c, ($container->privates['Module\\Common\\Factory\\ResponseFactory'] ?? $container->load('getResponseFactoryService')));
    }
}