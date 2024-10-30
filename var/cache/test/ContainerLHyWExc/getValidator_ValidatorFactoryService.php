<?php

namespace ContainerLHyWExc;


use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;

/**
 * @internal This class has been auto-generated by the Symfony Dependency Injection Component.
 */
class getValidator_ValidatorFactoryService extends Module_KernelTestDebugContainer
{
    /**
     * Gets the private 'validator.validator_factory' shared service.
     *
     * @return \Symfony\Component\Validator\ContainerConstraintValidatorFactory
     */
    public static function do($container, $lazyLoad = true)
    {
        include_once \dirname(__DIR__, 4).''.\DIRECTORY_SEPARATOR.'vendor'.\DIRECTORY_SEPARATOR.'symfony'.\DIRECTORY_SEPARATOR.'validator'.\DIRECTORY_SEPARATOR.'ConstraintValidatorFactoryInterface.php';
        include_once \dirname(__DIR__, 4).''.\DIRECTORY_SEPARATOR.'vendor'.\DIRECTORY_SEPARATOR.'symfony'.\DIRECTORY_SEPARATOR.'validator'.\DIRECTORY_SEPARATOR.'ContainerConstraintValidatorFactory.php';

        return $container->privates['validator.validator_factory'] = new \Symfony\Component\Validator\ContainerConstraintValidatorFactory(new \Symfony\Component\DependencyInjection\Argument\ServiceLocator($container->getService ??= $container->getService(...), [
            'validator.expression' => ['privates', 'validator.expression', 'getValidator_ExpressionService', true],
            'Symfony\\Component\\Validator\\Constraints\\ExpressionValidator' => ['privates', 'validator.expression', 'getValidator_ExpressionService', true],
            'Symfony\\Component\\Validator\\Constraints\\EmailValidator' => ['privates', 'validator.email', 'getValidator_EmailService', true],
            'Symfony\\Component\\Validator\\Constraints\\NotCompromisedPasswordValidator' => ['privates', 'validator.not_compromised_password', 'getValidator_NotCompromisedPasswordService', true],
            'Symfony\\Component\\Validator\\Constraints\\WhenValidator' => ['privates', 'validator.when', 'getValidator_WhenService', true],
            'Symfony\\Component\\Validator\\Constraints\\NoSuspiciousCharactersValidator' => ['privates', 'validator.no_suspicious_characters', 'getValidator_NoSuspiciousCharactersService', true],
            'doctrine.orm.validator.unique' => ['privates', 'doctrine.orm.validator.unique', 'getDoctrine_Orm_Validator_UniqueService', true],
            'Symfony\\Bridge\\Doctrine\\Validator\\Constraints\\UniqueEntityValidator' => ['privates', 'doctrine.orm.validator.unique', 'getDoctrine_Orm_Validator_UniqueService', true],
        ], [
            'validator.expression' => '?',
            'Symfony\\Component\\Validator\\Constraints\\ExpressionValidator' => '?',
            'Symfony\\Component\\Validator\\Constraints\\EmailValidator' => '?',
            'Symfony\\Component\\Validator\\Constraints\\NotCompromisedPasswordValidator' => '?',
            'Symfony\\Component\\Validator\\Constraints\\WhenValidator' => '?',
            'Symfony\\Component\\Validator\\Constraints\\NoSuspiciousCharactersValidator' => '?',
            'doctrine.orm.validator.unique' => '?',
            'Symfony\\Bridge\\Doctrine\\Validator\\Constraints\\UniqueEntityValidator' => '?',
        ]));
    }
}
