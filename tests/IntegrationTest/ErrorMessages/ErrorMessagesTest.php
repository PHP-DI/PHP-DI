<?php

declare(strict_types=1);

namespace DI\Test\IntegrationTest\ErrorMessages;

use DI\ContainerBuilder;
use DI\Definition\Exception\InvalidDefinition;
use DI\Test\IntegrationTest\BaseContainerTest;
use function DI\autowire;

/**
 * Test error messages.
 */
class ErrorMessagesTest extends BaseContainerTest
{
    /**
     * @dataProvider provideContainer
     */
    public function test_non_instantiable_class(ContainerBuilder $builder)
    {
        $word = $builder->isCompilationEnabled() ? 'compiled' : 'resolved';
        $message = <<<MESSAGE
Entry "DI\Test\IntegrationTest\ErrorMessages\InterfaceFixture" cannot be $word: the class is not instantiable
Full definition:
Object (
    class = #NOT INSTANTIABLE# DI\Test\IntegrationTest\ErrorMessages\InterfaceFixture
    lazy = false
)
MESSAGE;
        $this->expectException(InvalidDefinition::class);
        $this->expectExceptionMessage($message);
        $builder->addDefinitions([
            InterfaceFixture::class => autowire(),
        ]);

        $builder->build()->get(InterfaceFixture::class);
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_non_existent_class(ContainerBuilder $builder)
    {
        $word = $builder->isCompilationEnabled() ? 'compiled' : 'resolved';
        $message = <<<MESSAGE
Entry "Acme\Foo\Bar\Bar" cannot be $word: the class doesn't exist
Full definition:
Object (
    class = #UNKNOWN# Acme\Foo\Bar\Bar
    lazy = false
)
MESSAGE;
        $this->expectException(InvalidDefinition::class);
        $this->expectExceptionMessage($message);

        $builder->addDefinitions([
            'Acme\Foo\Bar\Bar' => \DI\create(),
        ]);

        $builder->build()->get('Acme\Foo\Bar\Bar');
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_undefined_constructor_parameter(ContainerBuilder $builder)
    {
        $word = $builder->isCompilationEnabled() ? 'compiled' : 'resolved';
        $message = <<<MESSAGE
Entry "DI\Test\IntegrationTest\ErrorMessages\Buggy1" cannot be $word: Parameter \$bar of __construct() has no value defined or guessable
Full definition:
Object (
    class = DI\Test\IntegrationTest\ErrorMessages\Buggy1
    lazy = false
    __construct(
        \$foo = 'some value'
        \$bar = #UNDEFINED#
        \$default = (default value) 123
    )
)
MESSAGE;
        $this->expectException(InvalidDefinition::class);
        $this->expectExceptionMessage($message);

        $builder->addDefinitions([
            Buggy1::class => \DI\autowire()->constructorParameter('foo', 'some value'),
        ]);
        $container = $builder->build();

        $container->get(Buggy1::class);
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_constructor_injection_of_non_existent_container_entry(ContainerBuilder $builder)
    {
        $this->expectException('DI\DependencyException');
        $this->expectExceptionMessage('Error while injecting dependencies into DI\Test\IntegrationTest\ErrorMessages\Buggy2: No entry or class found for \'nonExistentEntry\'');
        $builder->useAnnotations(true);
        $builder->build()->get(Buggy2::class);
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_property_injection_of_non_existent_container_entry(ContainerBuilder $builder)
    {
        $this->expectException('DI\DependencyException');
        $this->expectExceptionMessage('Error while injecting in DI\Test\IntegrationTest\ErrorMessages\Buggy3::dependency. No entry or class found for \'namedDependency\'');
        $builder->useAnnotations(true);
        $builder->build()->get(Buggy3::class);
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_setter_injection_of_non_existent_container_entry(ContainerBuilder $builder)
    {
        $this->expectException('DI\DependencyException');
        $this->expectExceptionMessage('Error while injecting dependencies into DI\Test\IntegrationTest\ErrorMessages\Buggy4: No entry or class found for \'nonExistentBean\'');
        $builder->useAnnotations(true);
        $builder->build()->get(Buggy4::class);
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_setter_injection_not_type_hinted(ContainerBuilder $builder)
    {
        $word = $builder->isCompilationEnabled() ? 'compiled' : 'resolved';
        $message = <<<MESSAGE
Entry "DI\Test\IntegrationTest\ErrorMessages\Buggy5" cannot be $word: Parameter \$dependency of setDependency() has no value defined or guessable
Full definition:
Object (
    class = DI\Test\IntegrationTest\ErrorMessages\Buggy5
    lazy = false
    setDependency(
        \$dependency = #UNDEFINED#
    )
)
MESSAGE;
        $this->expectException(InvalidDefinition::class);
        $this->expectExceptionMessage($message);

        $builder->useAnnotations(true);
        $builder->addDefinitions([
            Buggy5::class => \DI\autowire(),
        ]);

        $builder->build()->get(Buggy5::class);
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_factory_not_callable(ContainerBuilder $builder)
    {
        $this->expectException('DI\Definition\Exception\InvalidDefinition');
        $this->expectExceptionMessage('Entry "foo" cannot be resolved: factory \'bar\' is neither a callable nor a valid container entry');
        $builder->addDefinitions([
            'foo' => \DI\factory('bar'),
        ]);
        $builder->build()->get('foo');
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_internal_class_default_parameter_value(ContainerBuilder $builder)
    {
        $this->expectException('DI\Definition\Exception\InvalidDefinition');
        $this->expectExceptionMessage('The parameter "time" of __construct() has no type defined or guessable. It has a default value, but the default value can\'t be read through Reflection because it is a PHP internal class.');
        $builder->addDefinitions([
            \DateTime::class => autowire(),
        ]);
        $builder->build()->get(\DateTime::class);
    }
}
