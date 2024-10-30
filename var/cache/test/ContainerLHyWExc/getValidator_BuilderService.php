<?php

namespace ContainerLHyWExc;


use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;

/**
 * @internal This class has been auto-generated by the Symfony Dependency Injection Component.
 */
class getValidator_BuilderService extends Module_KernelTestDebugContainer
{
    /**
     * Gets the private 'validator.builder' shared service.
     *
     * @return \Symfony\Component\Validator\ValidatorBuilder
     */
    public static function do($container, $lazyLoad = true)
    {
        include_once \dirname(__DIR__, 4).''.\DIRECTORY_SEPARATOR.'vendor'.\DIRECTORY_SEPARATOR.'symfony'.\DIRECTORY_SEPARATOR.'validator'.\DIRECTORY_SEPARATOR.'ValidatorBuilder.php';
        include_once \dirname(__DIR__, 4).''.\DIRECTORY_SEPARATOR.'vendor'.\DIRECTORY_SEPARATOR.'symfony'.\DIRECTORY_SEPARATOR.'validator'.\DIRECTORY_SEPARATOR.'Validation.php';

        $container->privates['validator.builder'] = $instance = \Symfony\Component\Validator\Validation::createValidatorBuilder();

        $instance->setConstraintValidatorFactory(($container->privates['validator.validator_factory'] ?? $container->load('getValidator_ValidatorFactoryService')));
        $instance->setGroupProviderLocator(($container->privates['.service_locator.GIuJv7e'] ??= new \Symfony\Component\DependencyInjection\Argument\ServiceLocator($container->getService ??= $container->getService(...), [], [])));
        $instance->setTranslationDomain('validators');
        $instance->enableAttributeMapping();
        $instance->addMethodMapping('loadValidatorMetadata');
        $instance->addObjectInitializers([($container->privates['doctrine.orm.validator_initializer'] ?? $container->load('getDoctrine_Orm_ValidatorInitializerService'))]);
        $instance->addLoader(($container->privates['doctrine.orm.default_entity_manager.validator_loader'] ?? $container->load('getDoctrine_Orm_DefaultEntityManager_ValidatorLoaderService')));

        return $instance;
    }
}
