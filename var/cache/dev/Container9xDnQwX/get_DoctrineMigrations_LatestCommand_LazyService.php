<?php

namespace Container9xDnQwX;


use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;

/**
 * @internal This class has been auto-generated by the Symfony Dependency Injection Component.
 */
class get_DoctrineMigrations_LatestCommand_LazyService extends Module_KernelDevDebugContainer
{
    /**
     * Gets the private '.doctrine_migrations.latest_command.lazy' shared service.
     *
     * @return \Symfony\Component\Console\Command\LazyCommand
     */
    public static function do($container, $lazyLoad = true)
    {
        include_once \dirname(__DIR__, 4).''.\DIRECTORY_SEPARATOR.'vendor'.\DIRECTORY_SEPARATOR.'symfony'.\DIRECTORY_SEPARATOR.'console'.\DIRECTORY_SEPARATOR.'Command'.\DIRECTORY_SEPARATOR.'Command.php';
        include_once \dirname(__DIR__, 4).''.\DIRECTORY_SEPARATOR.'vendor'.\DIRECTORY_SEPARATOR.'symfony'.\DIRECTORY_SEPARATOR.'console'.\DIRECTORY_SEPARATOR.'Command'.\DIRECTORY_SEPARATOR.'LazyCommand.php';

        return $container->privates['.doctrine_migrations.latest_command.lazy'] = new \Symfony\Component\Console\Command\LazyCommand('doctrine:migrations:latest', [], 'Outputs the latest version', false, #[\Closure(name: 'doctrine_migrations.latest_command', class: 'Doctrine\\Migrations\\Tools\\Console\\Command\\LatestCommand')] fn (): \Doctrine\Migrations\Tools\Console\Command\LatestCommand => ($container->privates['doctrine_migrations.latest_command'] ?? $container->load('getDoctrineMigrations_LatestCommandService')));
    }
}
