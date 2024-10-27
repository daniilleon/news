<?php

namespace ContainerG3wh20C;

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;

/**
 * @internal This class has been auto-generated by the Symfony Dependency Injection Component.
 */
class getEmployeeControllerService extends Module_KernelTestDebugContainer
{
    /**
     * Gets the public 'Module\Employees\Controller\Api\EmployeeController' shared autowired service.
     *
     * @return \Module\Employees\Controller\Api\EmployeeController
     */
    public static function do($container, $lazyLoad = true)
    {
        include_once \dirname(__DIR__, 4).''.\DIRECTORY_SEPARATOR.'module'.\DIRECTORY_SEPARATOR.'Employees'.\DIRECTORY_SEPARATOR.'Controller'.\DIRECTORY_SEPARATOR.'Api'.\DIRECTORY_SEPARATOR.'EmployeeController.php';

        return $container->services['Module\\Employees\\Controller\\Api\\EmployeeController'] = new \Module\Employees\Controller\Api\EmployeeController(($container->privates['Module\\Employees\\Service\\EmployeeService'] ?? $container->load('getEmployeeServiceService')), ($container->privates['monolog.logger'] ?? $container->load('getMonolog_LoggerService')));
    }
}
