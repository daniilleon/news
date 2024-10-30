<?php

namespace ContainerFCuexxn;


use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;

/**
 * @internal This class has been auto-generated by the Symfony Dependency Injection Component.
 */
class getDoctrine_Dbal_ConnectionFactoryService extends Module_KernelTestDebugContainer
{
    /**
     * Gets the private 'doctrine.dbal.connection_factory' shared service.
     *
     * @return \Doctrine\Bundle\DoctrineBundle\ConnectionFactory
     */
    public static function do($container, $lazyLoad = true)
    {
        include_once \dirname(__DIR__, 4).''.\DIRECTORY_SEPARATOR.'vendor'.\DIRECTORY_SEPARATOR.'doctrine'.\DIRECTORY_SEPARATOR.'doctrine-bundle'.\DIRECTORY_SEPARATOR.'src'.\DIRECTORY_SEPARATOR.'ConnectionFactory.php';

        return $container->privates['doctrine.dbal.connection_factory'] = new \Doctrine\Bundle\DoctrineBundle\ConnectionFactory([], ($container->privates['doctrine.dbal.connection_factory.dsn_parser'] ?? $container->load('getDoctrine_Dbal_ConnectionFactory_DsnParserService')));
    }
}
