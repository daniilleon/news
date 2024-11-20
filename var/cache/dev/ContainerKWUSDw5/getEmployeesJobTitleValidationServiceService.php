<?php

namespace ContainerKWUSDw5;

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;

/**
 * @internal This class has been auto-generated by the Symfony Dependency Injection Component.
 */
class getEmployeesJobTitleValidationServiceService extends Module_KernelDevDebugContainer
{
    /**
     * Gets the private 'Module\Employees\EmployeesJobTitle\Service\EmployeesJobTitleValidationService' shared autowired service.
     *
     * @return \Module\Employees\EmployeesJobTitle\Service\EmployeesJobTitleValidationService
     */
    public static function do($container, $lazyLoad = true)
    {
        include_once \dirname(__DIR__, 4).''.\DIRECTORY_SEPARATOR.'module'.\DIRECTORY_SEPARATOR.'Employees'.\DIRECTORY_SEPARATOR.'EmployeesJobTitle'.\DIRECTORY_SEPARATOR.'Service'.\DIRECTORY_SEPARATOR.'EmployeesJobTitleValidationService.php';

        return $container->privates['Module\\Employees\\EmployeesJobTitle\\Service\\EmployeesJobTitleValidationService'] = new \Module\Employees\EmployeesJobTitle\Service\EmployeesJobTitleValidationService(($container->privates['Module\\Employees\\EmployeesJobTitle\\Repository\\EmployeesJobTitleRepository'] ?? $container->load('getEmployeesJobTitleRepositoryService')), ($container->privates['Module\\Employees\\EmployeesJobTitle\\Repository\\EmployeeJobTitleTranslationsRepository'] ?? $container->load('getEmployeeJobTitleTranslationsRepositoryService')), ($container->privates['Module\\Common\\Proxy\\Core\\LanguagesProxyService'] ?? $container->load('getLanguagesProxyServiceService')), ($container->privates['monolog.logger'] ?? $container->load('getMonolog_LoggerService')));
    }
}
