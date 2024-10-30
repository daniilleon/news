<?php

namespace ContainerLHyWExc;


use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;

/**
 * @internal This class has been auto-generated by the Symfony Dependency Injection Component.
 */
class getMaker_AutoloaderUtilService extends Module_KernelTestDebugContainer
{
    /**
     * Gets the private 'maker.autoloader_util' shared service.
     *
     * @return \Symfony\Bundle\MakerBundle\Util\AutoloaderUtil
     */
    public static function do($container, $lazyLoad = true)
    {
        include_once \dirname(__DIR__, 4).''.\DIRECTORY_SEPARATOR.'vendor'.\DIRECTORY_SEPARATOR.'symfony'.\DIRECTORY_SEPARATOR.'maker-bundle'.\DIRECTORY_SEPARATOR.'src'.\DIRECTORY_SEPARATOR.'Util'.\DIRECTORY_SEPARATOR.'AutoloaderUtil.php';
        include_once \dirname(__DIR__, 4).''.\DIRECTORY_SEPARATOR.'vendor'.\DIRECTORY_SEPARATOR.'symfony'.\DIRECTORY_SEPARATOR.'maker-bundle'.\DIRECTORY_SEPARATOR.'src'.\DIRECTORY_SEPARATOR.'Util'.\DIRECTORY_SEPARATOR.'ComposerAutoloaderFinder.php';

        return $container->privates['maker.autoloader_util'] = new \Symfony\Bundle\MakerBundle\Util\AutoloaderUtil(($container->privates['maker.autoloader_finder'] ??= new \Symfony\Bundle\MakerBundle\Util\ComposerAutoloaderFinder('Module')));
    }
}
