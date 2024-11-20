<?php

namespace ContainerKWUSDw5;

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;

/**
 * @internal This class has been auto-generated by the Symfony Dependency Injection Component.
 */
class getCategoriesValidationServiceService extends Module_KernelDevDebugContainer
{
    /**
     * Gets the private 'Module\Core\Categories\Service\CategoriesValidationService' shared autowired service.
     *
     * @return \Module\Core\Categories\Service\CategoriesValidationService
     */
    public static function do($container, $lazyLoad = true)
    {
        include_once \dirname(__DIR__, 4).''.\DIRECTORY_SEPARATOR.'module'.\DIRECTORY_SEPARATOR.'Core'.\DIRECTORY_SEPARATOR.'Categories'.\DIRECTORY_SEPARATOR.'Service'.\DIRECTORY_SEPARATOR.'CategoriesValidationService.php';

        return $container->privates['Module\\Core\\Categories\\Service\\CategoriesValidationService'] = new \Module\Core\Categories\Service\CategoriesValidationService(($container->privates['Module\\Core\\Categories\\Repository\\CategoriesRepository'] ?? $container->load('getCategoriesRepositoryService')), ($container->privates['Module\\Core\\Categories\\Repository\\CategoryTranslationsRepository'] ?? $container->load('getCategoryTranslationsRepositoryService')), ($container->privates['Module\\Common\\Proxy\\Core\\LanguagesProxyService'] ?? $container->load('getLanguagesProxyServiceService')), ($container->privates['monolog.logger'] ?? $container->load('getMonolog_LoggerService')));
    }
}
