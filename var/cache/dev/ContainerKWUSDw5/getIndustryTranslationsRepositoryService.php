<?php

namespace ContainerKWUSDw5;

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;

/**
 * @internal This class has been auto-generated by the Symfony Dependency Injection Component.
 */
class getIndustryTranslationsRepositoryService extends Module_KernelDevDebugContainer
{
    /**
     * Gets the private 'Module\Shared\Industries\Repository\IndustryTranslationsRepository' shared autowired service.
     *
     * @return \Module\Shared\Industries\Repository\IndustryTranslationsRepository
     */
    public static function do($container, $lazyLoad = true)
    {
        include_once \dirname(__DIR__, 4).''.\DIRECTORY_SEPARATOR.'vendor'.\DIRECTORY_SEPARATOR.'doctrine'.\DIRECTORY_SEPARATOR.'persistence'.\DIRECTORY_SEPARATOR.'src'.\DIRECTORY_SEPARATOR.'Persistence'.\DIRECTORY_SEPARATOR.'ObjectRepository.php';
        include_once \dirname(__DIR__, 4).''.\DIRECTORY_SEPARATOR.'vendor'.\DIRECTORY_SEPARATOR.'doctrine'.\DIRECTORY_SEPARATOR.'collections'.\DIRECTORY_SEPARATOR.'src'.\DIRECTORY_SEPARATOR.'Selectable.php';
        include_once \dirname(__DIR__, 4).''.\DIRECTORY_SEPARATOR.'vendor'.\DIRECTORY_SEPARATOR.'doctrine'.\DIRECTORY_SEPARATOR.'orm'.\DIRECTORY_SEPARATOR.'src'.\DIRECTORY_SEPARATOR.'EntityRepository.php';
        include_once \dirname(__DIR__, 4).''.\DIRECTORY_SEPARATOR.'vendor'.\DIRECTORY_SEPARATOR.'doctrine'.\DIRECTORY_SEPARATOR.'doctrine-bundle'.\DIRECTORY_SEPARATOR.'src'.\DIRECTORY_SEPARATOR.'Repository'.\DIRECTORY_SEPARATOR.'ServiceEntityRepositoryInterface.php';
        include_once \dirname(__DIR__, 4).''.\DIRECTORY_SEPARATOR.'vendor'.\DIRECTORY_SEPARATOR.'doctrine'.\DIRECTORY_SEPARATOR.'doctrine-bundle'.\DIRECTORY_SEPARATOR.'src'.\DIRECTORY_SEPARATOR.'Repository'.\DIRECTORY_SEPARATOR.'ServiceEntityRepositoryProxy.php';
        include_once \dirname(__DIR__, 4).''.\DIRECTORY_SEPARATOR.'vendor'.\DIRECTORY_SEPARATOR.'doctrine'.\DIRECTORY_SEPARATOR.'doctrine-bundle'.\DIRECTORY_SEPARATOR.'src'.\DIRECTORY_SEPARATOR.'Repository'.\DIRECTORY_SEPARATOR.'ServiceEntityRepository.php';
        include_once \dirname(__DIR__, 4).''.\DIRECTORY_SEPARATOR.'module'.\DIRECTORY_SEPARATOR.'Shared'.\DIRECTORY_SEPARATOR.'Industries'.\DIRECTORY_SEPARATOR.'Repository'.\DIRECTORY_SEPARATOR.'IndustryTranslationsRepository.php';

        $a = ($container->services['doctrine.orm.default_entity_manager'] ?? $container->load('getDoctrine_Orm_DefaultEntityManagerService'));

        if (isset($container->privates['Module\\Shared\\Industries\\Repository\\IndustryTranslationsRepository'])) {
            return $container->privates['Module\\Shared\\Industries\\Repository\\IndustryTranslationsRepository'];
        }

        return $container->privates['Module\\Shared\\Industries\\Repository\\IndustryTranslationsRepository'] = new \Module\Shared\Industries\Repository\IndustryTranslationsRepository(($container->services['doctrine'] ?? $container->load('getDoctrineService')), $a);
    }
}