<?php

namespace ContainerLHyWExc;


use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;

/**
 * @internal This class has been auto-generated by the Symfony Dependency Injection Component.
 */
class getMaker_Maker_MakeMessageService extends Module_KernelTestDebugContainer
{
    /**
     * Gets the private 'maker.maker.make_message' shared service.
     *
     * @return \Symfony\Bundle\MakerBundle\Maker\MakeMessage
     */
    public static function do($container, $lazyLoad = true)
    {
        include_once \dirname(__DIR__, 4).''.\DIRECTORY_SEPARATOR.'vendor'.\DIRECTORY_SEPARATOR.'symfony'.\DIRECTORY_SEPARATOR.'maker-bundle'.\DIRECTORY_SEPARATOR.'src'.\DIRECTORY_SEPARATOR.'MakerInterface.php';
        include_once \dirname(__DIR__, 4).''.\DIRECTORY_SEPARATOR.'vendor'.\DIRECTORY_SEPARATOR.'symfony'.\DIRECTORY_SEPARATOR.'maker-bundle'.\DIRECTORY_SEPARATOR.'src'.\DIRECTORY_SEPARATOR.'Maker'.\DIRECTORY_SEPARATOR.'AbstractMaker.php';
        include_once \dirname(__DIR__, 4).''.\DIRECTORY_SEPARATOR.'vendor'.\DIRECTORY_SEPARATOR.'symfony'.\DIRECTORY_SEPARATOR.'maker-bundle'.\DIRECTORY_SEPARATOR.'src'.\DIRECTORY_SEPARATOR.'Maker'.\DIRECTORY_SEPARATOR.'MakeMessage.php';

        return $container->privates['maker.maker.make_message'] = new \Symfony\Bundle\MakerBundle\Maker\MakeMessage(($container->privates['maker.file_manager'] ?? $container->load('getMaker_FileManagerService')));
    }
}
