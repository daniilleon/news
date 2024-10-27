<?php

namespace ContainerCI7iQwS;

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;

/**
 * @internal This class has been auto-generated by the Symfony Dependency Injection Component.
 */
class getDoctrine_Dbal_DefaultConnection_ConfigurationService extends Module_KernelTestDebugContainer
{
    /**
     * Gets the private 'doctrine.dbal.default_connection.configuration' shared service.
     *
     * @return \Doctrine\DBAL\Configuration
     */
    public static function do($container, $lazyLoad = true)
    {
        include_once \dirname(__DIR__, 4).''.\DIRECTORY_SEPARATOR.'vendor'.\DIRECTORY_SEPARATOR.'doctrine'.\DIRECTORY_SEPARATOR.'dbal'.\DIRECTORY_SEPARATOR.'src'.\DIRECTORY_SEPARATOR.'Configuration.php';
        include_once \dirname(__DIR__, 4).''.\DIRECTORY_SEPARATOR.'vendor'.\DIRECTORY_SEPARATOR.'doctrine'.\DIRECTORY_SEPARATOR.'dbal'.\DIRECTORY_SEPARATOR.'src'.\DIRECTORY_SEPARATOR.'Schema'.\DIRECTORY_SEPARATOR.'SchemaManagerFactory.php';
        include_once \dirname(__DIR__, 4).''.\DIRECTORY_SEPARATOR.'vendor'.\DIRECTORY_SEPARATOR.'doctrine'.\DIRECTORY_SEPARATOR.'dbal'.\DIRECTORY_SEPARATOR.'src'.\DIRECTORY_SEPARATOR.'Schema'.\DIRECTORY_SEPARATOR.'DefaultSchemaManagerFactory.php';

        $container->privates['doctrine.dbal.default_connection.configuration'] = $instance = new \Doctrine\DBAL\Configuration();

        $instance->setSchemaManagerFactory(($container->privates['doctrine.dbal.default_schema_manager_factory'] ??= new \Doctrine\DBAL\Schema\DefaultSchemaManagerFactory()));
        $instance->setMiddlewares([($container->privates['doctrine.dbal.logging_middleware.default'] ?? $container->load('getDoctrine_Dbal_LoggingMiddleware_DefaultService')), ($container->privates['doctrine.dbal.debug_middleware.default'] ?? $container->load('getDoctrine_Dbal_DebugMiddleware_DefaultService')), ($container->privates['doctrine.dbal.idle_connection_middleware.default'] ?? $container->load('getDoctrine_Dbal_IdleConnectionMiddleware_DefaultService'))]);

        return $instance;
    }
}