<?php

namespace ContainerG3wh20C;

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;

/**
 * @internal This class has been auto-generated by the Symfony Dependency Injection Component.
 */
class getTest_ServiceContainerService extends Module_KernelTestDebugContainer
{
    /**
     * Gets the public 'test.service_container' shared service.
     *
     * @return \Symfony\Bundle\FrameworkBundle\Test\TestContainer
     */
    public static function do($container, $lazyLoad = true)
    {
        return $container->services['test.service_container'] = new \Symfony\Bundle\FrameworkBundle\Test\TestContainer(($container->services['kernel'] ?? $container->get('kernel', 1)), 'test.private_services_locator', ['cache.default_clearer' => 'cache.app_clearer', 'debug.event_dispatcher' => 'debug.event_dispatcher.inner', 'debug.controller_resolver' => 'debug.controller_resolver.inner', 'debug.argument_resolver' => 'debug.argument_resolver.inner', 'router.default' => 'router', 'Symfony\\Component\\DependencyInjection\\ParameterBag\\ContainerBagInterface' => 'parameter_bag', 'Symfony\\Component\\DependencyInjection\\ParameterBag\\ParameterBagInterface' => 'parameter_bag', 'Symfony\\Component\\EventDispatcher\\EventDispatcherInterface' => 'debug.event_dispatcher.inner', 'Symfony\\Contracts\\EventDispatcher\\EventDispatcherInterface' => 'debug.event_dispatcher.inner', 'Psr\\EventDispatcher\\EventDispatcherInterface' => 'debug.event_dispatcher.inner', 'Symfony\\Component\\HttpKernel\\HttpKernelInterface' => 'http_kernel', 'Symfony\\Component\\HttpFoundation\\RequestStack' => 'request_stack', 'Symfony\\Component\\HttpFoundation\\UrlHelper' => 'url_helper', 'Symfony\\Component\\HttpKernel\\KernelInterface' => 'kernel', 'Symfony\\Component\\Filesystem\\Filesystem' => 'filesystem', 'Symfony\\Component\\HttpKernel\\Config\\FileLocator' => 'file_locator', 'Symfony\\Component\\HttpFoundation\\UriSigner' => 'uri_signer', 'Symfony\\Component\\String\\Slugger\\SluggerInterface' => 'slugger', 'Symfony\\Component\\HttpKernel\\Fragment\\FragmentUriGeneratorInterface' => 'fragment.uri_generator', 'error_renderer.html' => 'twig.error_renderer.html', 'error_renderer' => 'twig.error_renderer.html', 'Psr\\Container\\ContainerInterface $parameterBag' => 'parameter_bag', 'Psr\\Cache\\CacheItemPoolInterface' => 'cache.app', 'Symfony\\Contracts\\Cache\\CacheInterface' => 'cache.app', 'Symfony\\Contracts\\Cache\\TagAwareCacheInterface' => 'cache.app.taggable', 'Symfony\\Component\\ErrorHandler\\ErrorRenderer\\FileLinkFormatter' => 'debug.file_link_formatter', 'Symfony\\Component\\Stopwatch\\Stopwatch' => 'debug.stopwatch', 'Symfony\\Component\\Routing\\RouterInterface' => 'router', 'Symfony\\Component\\Routing\\Generator\\UrlGeneratorInterface' => 'router', 'Symfony\\Component\\Routing\\Matcher\\UrlMatcherInterface' => 'router', 'Symfony\\Component\\Routing\\RequestContextAwareInterface' => 'router', 'Symfony\\Component\\Routing\\RequestContext' => 'router.request_context', 'cache.default_doctrine_dbal_provider' => 'doctrine.dbal.default_connection', 'SessionHandlerInterface' => 'session.handler.native', 'session.storage.factory' => 'session.storage.factory.mock_file', 'session.handler' => 'session.handler.native', 'session_listener' => 'test.session.listener', 'Twig\\Environment' => 'twig', 'Doctrine\\DBAL\\Connection' => 'doctrine.dbal.default_connection', 'Doctrine\\Persistence\\ManagerRegistry' => 'doctrine', 'Doctrine\\Common\\Persistence\\ManagerRegistry' => 'doctrine', 'doctrine.dbal.event_manager' => 'doctrine.dbal.default_connection.event_manager', 'Doctrine\\DBAL\\Connection $defaultConnection' => 'doctrine.dbal.default_connection', 'Doctrine\\ORM\\EntityManagerInterface' => 'doctrine.orm.default_entity_manager', 'doctrine.orm.default_metadata_cache' => 'cache.doctrine.orm.default.metadata', 'doctrine.orm.default_result_cache' => 'cache.doctrine.orm.default.result', 'doctrine.orm.default_query_cache' => 'cache.doctrine.orm.default.query', 'Doctrine\\ORM\\EntityManagerInterface $defaultEntityManager' => 'doctrine.orm.default_entity_manager', 'doctrine.orm.default_entity_manager.event_manager' => 'doctrine.dbal.default_connection.event_manager', 'doctrine.migrations.metadata_storage' => 'doctrine.migrations.storage.table_storage', 'logger' => 'monolog.logger', 'Psr\\Log\\LoggerInterface' => 'monolog.logger', 'twig.loader.filesystem' => 'twig.loader.native_filesystem', 'argument_resolver.controller_locator' => '.service_locator.s2ToKAb', 'twig.loader' => 'twig.loader.native_filesystem', 'doctrine.id_generator_locator' => '.service_locator.BxSdgVt', 'Psr\\Log\\LoggerInterface $requestLogger' => 'monolog.logger.request', 'Psr\\Log\\LoggerInterface $consoleLogger' => 'monolog.logger.console', 'Psr\\Log\\LoggerInterface $cacheLogger' => 'monolog.logger.cache', 'Psr\\Log\\LoggerInterface $phpLogger' => 'monolog.logger.php', 'Psr\\Log\\LoggerInterface $eventLogger' => 'monolog.logger.event', 'Psr\\Log\\LoggerInterface $routerLogger' => 'monolog.logger.router', 'Psr\\Log\\LoggerInterface $doctrineLogger' => 'monolog.logger.doctrine', 'Psr\\Log\\LoggerInterface $deprecationLogger' => 'monolog.logger.deprecation', 'controller_resolver' => 'debug.controller_resolver.inner', 'argument_resolver' => 'debug.argument_resolver.inner', 'twig.error_renderer.html.inner' => 'error_handler.error_renderer.html', 'doctrine.orm.default_metadata_driver' => '.doctrine.orm.default_metadata_driver.inner', 'Module\\Kernel' => 'kernel', 'database_connection' => 'doctrine.dbal.default_connection', 'doctrine.orm.entity_manager' => 'doctrine.orm.default_entity_manager']);
    }
}