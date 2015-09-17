<?php
namespace DI\Test\IntegrationTest\ErrorMessages;

use DI\ContainerBuilder;

/**
 * @coversNothing
 */
class ErrorMessagesTest extends \PHPUnit_Framework_TestCase
{
    public function test_non_instantiable_class()
    {
        $message = <<<'MESSAGE'
Entry DI\Test\IntegrationTest\ErrorMessages\InterfaceFixture cannot be resolved: the class is not instantiable
Full definition:
Object (
    class = #NOT INSTANTIABLE# DI\Test\IntegrationTest\ErrorMessages\InterfaceFixture
    scope = singleton
    lazy = false
)
MESSAGE;
        $this->setExpectedException('DI\Definition\Exception\DefinitionException', $message);

        $container = ContainerBuilder::buildDevContainer();
        $container->get('DI\Test\IntegrationTest\ErrorMessages\InterfaceFixture');
    }

    public function test_non_existent_class()
    {
        $message = <<<'MESSAGE'
Entry Acme\Foo\Bar\Bar cannot be resolved: the class doesn't exist
Full definition:
Object (
    class = #UNKNOWN# Acme\Foo\Bar\Bar
    scope = singleton
    lazy = false
)
MESSAGE;
        $this->setExpectedException('DI\Definition\Exception\DefinitionException', $message);

        $container = ContainerBuilder::buildDevContainer();
        $container->set('Acme\Foo\Bar\Bar', \DI\object());
        $container->get('Acme\Foo\Bar\Bar');
    }

    public function test_undefined_constructor_parameter()
    {
        $message = <<<'MESSAGE'
Entry DI\Test\IntegrationTest\ErrorMessages\Buggy1 cannot be resolved: Parameter $bar of __construct() has no value defined or guessable
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
        $this->setExpectedException('DI\Definition\Exception\DefinitionException', $message);

        $container = ContainerBuilder::buildDevContainer();
        $container->set(
            'DI\Test\IntegrationTest\ErrorMessages\Buggy1',
            \DI\object()->constructorParameter('foo', 'some value')
        );

        $container->get('DI\Test\IntegrationTest\ErrorMessages\Buggy1');
    }

    /**
     * @expectedException \DI\DependencyException
     * @expectedExceptionMessage Error while injecting dependencies into DI\Test\IntegrationTest\ErrorMessages\Buggy2: No entry or class found for 'nonExistentEntry'
     */
    public function test_constructor_injection_of_non_existent_container_entry()
    {
        $builder = new ContainerBuilder();
        $builder->useAnnotations(true);
        $container = $builder->build();
        $container->get('DI\Test\IntegrationTest\ErrorMessages\Buggy2');
    }

    /**
     * @expectedException \DI\DependencyException
     * @expectedExceptionMessage Error while injecting in DI\Test\IntegrationTest\ErrorMessages\Buggy3::dependency. No entry or class found for 'namedDependency'
     */
    public function test_property_injection_of_non_existent_container_entry()
    {
        $builder = new ContainerBuilder();
        $builder->useAnnotations(true);
        $container = $builder->build();
        $container->get('DI\Test\IntegrationTest\ErrorMessages\Buggy3');
    }

    /**
     * @expectedException \DI\DependencyException
     * @expectedExceptionMessage Error while injecting dependencies into DI\Test\IntegrationTest\ErrorMessages\Buggy4: No entry or class found for 'nonExistentBean'
     */
    public function test_setter_injection_of_non_existent_container_entry()
    {
        $builder = new ContainerBuilder();
        $builder->useAnnotations(true);
        $container = $builder->build();
        $container->get('DI\Test\IntegrationTest\ErrorMessages\Buggy4');
    }

    public function test_setter_injection_not_type_hinted()
    {
        $message = <<<'MESSAGE'
Entry DI\Test\IntegrationTest\ErrorMessages\Buggy5 cannot be resolved: Parameter $dependency of setDependency() has no value defined or guessable
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
        $this->setExpectedException('DI\Definition\Exception\DefinitionException', $message);

        $builder = new ContainerBuilder();
        $builder->useAnnotations(true);
        $container = $builder->build();
        $container->get('DI\Test\IntegrationTest\ErrorMessages\Buggy5');
    }
}
