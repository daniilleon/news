<?php

namespace ContainerCI7iQwS;

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;

/**
 * @internal This class has been auto-generated by the Symfony Dependency Injection Component.
 */
class getDebug_FileLinkFormatterService extends Module_KernelTestDebugContainer
{
    /**
     * Gets the private 'debug.file_link_formatter' shared service.
     *
     * @return \Symfony\Component\ErrorHandler\ErrorRenderer\FileLinkFormatter
     */
    public static function do($container, $lazyLoad = true)
    {
        include_once \dirname(__DIR__, 4).''.\DIRECTORY_SEPARATOR.'vendor'.\DIRECTORY_SEPARATOR.'symfony'.\DIRECTORY_SEPARATOR.'error-handler'.\DIRECTORY_SEPARATOR.'ErrorRenderer'.\DIRECTORY_SEPARATOR.'FileLinkFormatter.php';

        return $container->privates['debug.file_link_formatter'] = new \Symfony\Component\ErrorHandler\ErrorRenderer\FileLinkFormatter($container->getEnv('default::SYMFONY_IDE'));
    }
}
