<?php

namespace ContainerCI7iQwS;

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;

/**
 * @internal This class has been auto-generated by the Symfony Dependency Injection Component.
 */
class getMaker_AutoCommand_MakeFormService extends Module_KernelTestDebugContainer
{
    /**
     * Gets the private 'maker.auto_command.make_form' shared service.
     *
     * @return \Symfony\Bundle\MakerBundle\Command\MakerCommand
     */
    public static function do($container, $lazyLoad = true)
    {
        include_once \dirname(__DIR__, 4).''.\DIRECTORY_SEPARATOR.'vendor'.\DIRECTORY_SEPARATOR.'symfony'.\DIRECTORY_SEPARATOR.'console'.\DIRECTORY_SEPARATOR.'Command'.\DIRECTORY_SEPARATOR.'Command.php';
        include_once \dirname(__DIR__, 4).''.\DIRECTORY_SEPARATOR.'vendor'.\DIRECTORY_SEPARATOR.'symfony'.\DIRECTORY_SEPARATOR.'maker-bundle'.\DIRECTORY_SEPARATOR.'src'.\DIRECTORY_SEPARATOR.'Command'.\DIRECTORY_SEPARATOR.'MakerCommand.php';
        include_once \dirname(__DIR__, 4).''.\DIRECTORY_SEPARATOR.'vendor'.\DIRECTORY_SEPARATOR.'symfony'.\DIRECTORY_SEPARATOR.'maker-bundle'.\DIRECTORY_SEPARATOR.'src'.\DIRECTORY_SEPARATOR.'Util'.\DIRECTORY_SEPARATOR.'TemplateLinter.php';

        $container->privates['maker.auto_command.make_form'] = $instance = new \Symfony\Bundle\MakerBundle\Command\MakerCommand(($container->privates['maker.maker.make_form'] ?? $container->load('getMaker_Maker_MakeFormService')), ($container->privates['maker.file_manager'] ?? $container->load('getMaker_FileManagerService')), ($container->privates['maker.generator'] ?? $container->load('getMaker_GeneratorService')), ($container->privates['maker.template_linter'] ??= new \Symfony\Bundle\MakerBundle\Util\TemplateLinter($container->getEnv('default::string:MAKER_PHP_CS_FIXER_BINARY_PATH'), $container->getEnv('default::string:MAKER_PHP_CS_FIXER_CONFIG_PATH'))));

        $instance->setName('make:form');
        $instance->setDescription('Create a new form class');

        return $instance;
    }
}
