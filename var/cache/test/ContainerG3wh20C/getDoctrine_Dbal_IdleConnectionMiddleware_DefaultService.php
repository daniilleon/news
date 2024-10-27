<?php

namespace ContainerG3wh20C;

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;

/**
 * @internal This class has been auto-generated by the Symfony Dependency Injection Component.
 */
class getDoctrine_Dbal_IdleConnectionMiddleware_DefaultService extends Module_KernelTestDebugContainer
{
    /**
     * Gets the private 'doctrine.dbal.idle_connection_middleware.default' shared service.
     *
     * @return \Doctrine\Bundle\DoctrineBundle\Middleware\IdleConnectionMiddleware
     */
    public static function do($container, $lazyLoad = true)
    {
        include_once \dirname(__DIR__, 4).''.\DIRECTORY_SEPARATOR.'vendor'.\DIRECTORY_SEPARATOR.'doctrine'.\DIRECTORY_SEPARATOR.'dbal'.\DIRECTORY_SEPARATOR.'src'.\DIRECTORY_SEPARATOR.'Driver'.\DIRECTORY_SEPARATOR.'Middleware.php';
        include_once \dirname(__DIR__, 4).''.\DIRECTORY_SEPARATOR.'vendor'.\DIRECTORY_SEPARATOR.'doctrine'.\DIRECTORY_SEPARATOR.'doctrine-bundle'.\DIRECTORY_SEPARATOR.'src'.\DIRECTORY_SEPARATOR.'Middleware'.\DIRECTORY_SEPARATOR.'ConnectionNameAwareInterface.php';
        include_once \dirname(__DIR__, 4).''.\DIRECTORY_SEPARATOR.'vendor'.\DIRECTORY_SEPARATOR.'doctrine'.\DIRECTORY_SEPARATOR.'doctrine-bundle'.\DIRECTORY_SEPARATOR.'src'.\DIRECTORY_SEPARATOR.'Middleware'.\DIRECTORY_SEPARATOR.'IdleConnectionMiddleware.php';

        $container->privates['doctrine.dbal.idle_connection_middleware.default'] = $instance = new \Doctrine\Bundle\DoctrineBundle\Middleware\IdleConnectionMiddleware(($container->privates['doctrine.dbal.connection_expiries'] ??= new \ArrayObject()), ['default' => 600]);

        $instance->setConnectionName('default');

        return $instance;
    }
}
