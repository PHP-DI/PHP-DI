<?php

namespace DI\Test\IntegrationTest\ErrorMessages;

use DI\ContainerBuilder;
use DI\Definition\Exception\InvalidDefinition;
use DI\Test\IntegrationTest\BaseContainerTest;

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
        $message = <<<'MESSAGE'
Entry "DI\Test\IntegrationTest\ErrorMessages\InterfaceFixture" cannot be resolved: the class is not instantiable
Full definition:
Object (
    class = #NOT INSTANTIABLE# DI\Test\IntegrationTest\ErrorMessages\InterfaceFixture
    scope = singleton
    lazy = false
)
MESSAGE;
        $this->expectException(InvalidDefinition::class);
        $this->expectExceptionMessage($message);

        $builder->build()->get('DI\Test\IntegrationTest\ErrorMessages\InterfaceFixture');
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_non_existent_class(ContainerBuilder $builder)
    {
        $message = <<<'MESSAGE'
Entry "Acme\Foo\Bar\Bar" cannot be resolved: the class doesn't exist
Full definition:
Object (
    class = #UNKNOWN# Acme\Foo\Bar\Bar
    scope = singleton
    lazy = false
)
MESSAGE;
        $this->setExpectedException(InvalidDefinition::class, $message);

        $container = $builder->build();
        $container->set('Acme\Foo\Bar\Bar', \DI\create());
        $container->get('Acme\Foo\Bar\Bar');
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_undefined_constructor_parameter(ContainerBuilder $builder)
    {
        $message = <<<'MESSAGE'
Entry "DI\Test\IntegrationTest\ErrorMessages\Buggy1" cannot be resolved: Parameter $bar of __construct() has no value defined or guessable
Full definition:
Object (
    class = DI\Test\IntegrationTest\ErrorMessages\Buggy1
    scope = singleton
    lazy = false
    __construct(
        $foo = 'some value'
        $bar = #UNDEFINED#
        $default = (default value) 123
    )
)
MESSAGE;
        $this->setExpectedException(InvalidDefinition::class, $message);

        $container = $builder->build();
        $container->set(Buggy1::class, \DI\autowire()->constructorParameter('foo', 'some value'));

        $container->get(Buggy1::class);
    }

    /**
     * @dataProvider provideContainer
     * @expectedException \DI\DependencyException
     * @expectedExceptionMessage Error while injecting dependencies into DI\Test\IntegrationTest\ErrorMessages\Buggy2: No entry or class found for 'nonExistentEntry'
     */
    public function test_constructor_injection_of_non_existent_container_entry(ContainerBuilder $builder)
    {
        $builder->useAnnotations(true);
        $builder->build()->get(Buggy2::class);
    }

    /**
     * @dataProvider provideContainer
     * @expectedException \DI\DependencyException
     * @expectedExceptionMessage Error while injecting in DI\Test\IntegrationTest\ErrorMessages\Buggy3::dependency. No entry or class found for 'namedDependency'
     */
    public function test_property_injection_of_non_existent_container_entry(ContainerBuilder $builder)
    {
        $builder->useAnnotations(true);
        $builder->build()->get(Buggy3::class);
    }

    /**
     * @dataProvider provideContainer
     * @expectedException \DI\DependencyException
     * @expectedExceptionMessage Error while injecting dependencies into DI\Test\IntegrationTest\ErrorMessages\Buggy4: No entry or class found for 'nonExistentBean'
     */
    public function test_setter_injection_of_non_existent_container_entry(ContainerBuilder $builder)
    {
        $builder->useAnnotations(true);
        $builder->build()->get(Buggy4::class);
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_setter_injection_not_type_hinted(ContainerBuilder $builder)
    {
        $message = <<<'MESSAGE'
Entry "DI\Test\IntegrationTest\ErrorMessages\Buggy5" cannot be resolved: Parameter $dependency of setDependency() has no value defined or guessable
Full definition:
Object (
    class = DI\Test\IntegrationTest\ErrorMessages\Buggy5
    scope = singleton
    lazy = false
    setDependency(
        $dependency = #UNDEFINED#
    )
)
MESSAGE;
        $this->setExpectedException(InvalidDefinition::class, $message);

        $builder->useAnnotations(true);
        $builder->build()->get(Buggy5::class);
    }

    /**
     * @dataProvider provideContainer
     * @expectedException \DI\Definition\Exception\InvalidDefinition
     * @expectedExceptionMessage Entry "foo" cannot be resolved: factory 'bar' is neither a callable nor a valid container entry
     */
    public function test_factory_not_callable(ContainerBuilder $builder)
    {
        $container = $builder->build();
        $container->set('foo', \DI\factory('bar'));
        $container->get('foo');
    }
}
