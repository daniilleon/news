<?php

namespace ContainerFCuexxn;


use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;

/**
 * @internal This class has been auto-generated by the Symfony Dependency Injection Component.
 */
class getDoctrine_Migrations_EntityManagerRegistryLoaderService extends Module_KernelTestDebugContainer
{
    /**
     * Gets the private 'doctrine.migrations.entity_manager_registry_loader' shared service.
     *
     * @return \Doctrine\Migrations\Configuration\EntityManager\ManagerRegistryEntityManager
     */
    public static function do($container, $lazyLoad = true)
    {
        include_once \dirname(__DIR__, 4).''.\DIRECTORY_SEPARATOR.'vendor'.\DIRECTORY_SEPARATOR.'doctrine'.\DIRECTORY_SEPARATOR.'migrations'.\DIRECTORY_SEPARATOR.'src'.\DIRECTORY_SEPARATOR.'Configuration'.\DIRECTORY_SEPARATOR.'EntityManager'.\DIRECTORY_SEPARATOR.'EntityManagerLoader.php';
        include_once \dirname(__DIR__, 4).''.\DIRECTORY_SEPARATOR.'vendor'.\DIRECTORY_SEPARATOR.'doctrine'.\DIRECTORY_SEPARATOR.'migrations'.\DIRECTORY_SEPARATOR.'src'.\DIRECTORY_SEPARATOR.'Configuration'.\DIRECTORY_SEPARATOR.'EntityManager'.\DIRECTORY_SEPARATOR.'ManagerRegistryEntityManager.php';

        return $container->privates['doctrine.migrations.entity_manager_registry_loader'] = \Doctrine\Migrations\Configuration\EntityManager\ManagerRegistryEntityManager::withSimpleDefault(($container->services['doctrine'] ?? $container->load('getDoctrineService')));
    }
}
