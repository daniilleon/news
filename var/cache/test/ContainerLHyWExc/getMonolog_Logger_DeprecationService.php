<?php

namespace ContainerLHyWExc;


use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;

/**
 * @internal This class has been auto-generated by the Symfony Dependency Injection Component.
 */
class getMonolog_Logger_DeprecationService extends Module_KernelTestDebugContainer
{
    /**
     * Gets the public 'monolog.logger.deprecation' shared service.
     *
     * @return \Monolog\Logger
     */
    public static function do($container, $lazyLoad = true)
    {
        $container->services['monolog.logger.deprecation'] = $instance = new \Monolog\Logger('deprecation');

        $instance->pushHandler(($container->privates['monolog.handler.main'] ?? self::getMonolog_Handler_MainService($container)));

        return $instance;
    }
}
