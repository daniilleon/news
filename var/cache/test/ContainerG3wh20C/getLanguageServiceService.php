<?php

namespace ContainerG3wh20C;

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;

/**
 * @internal This class has been auto-generated by the Symfony Dependency Injection Component.
 */
class getLanguageServiceService extends Module_KernelTestDebugContainer
{
    /**
     * Gets the private 'Module\Languages\Service\LanguageService' shared autowired service.
     *
     * @return \Module\Languages\Service\LanguageService
     */
    public static function do($container, $lazyLoad = true)
    {
        include_once \dirname(__DIR__, 4).''.\DIRECTORY_SEPARATOR.'module'.\DIRECTORY_SEPARATOR.'Languages'.\DIRECTORY_SEPARATOR.'Service'.\DIRECTORY_SEPARATOR.'LanguageService.php';

        return $container->privates['Module\\Languages\\Service\\LanguageService'] = new \Module\Languages\Service\LanguageService(($container->privates['Module\\Languages\\Repository\\LanguageRepository'] ?? $container->load('getLanguageRepositoryService')), ($container->privates['monolog.logger'] ?? $container->load('getMonolog_LoggerService')));
    }
}
