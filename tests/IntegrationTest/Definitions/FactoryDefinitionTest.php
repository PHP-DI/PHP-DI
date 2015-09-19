<?php

namespace DI\Test\IntegrationTest\Definitions;

use DI\ContainerBuilder;

/**
 * Test factory definitions.
 *
 * @coversNothing
 */
class FactoryDefinitionTest extends \PHPUnit_Framework_TestCase
{
    public function provideCallables()
    {
        return [
            'closure'               => [function () { return 'bar'; }],
            'function'              => [__NAMESPACE__ . '\FactoryDefinition_test'],
            'invokableObject'       => [new FactoryDefinitionInvokableTestClass],
            'invokableClass'        => [__NAMESPACE__ . '\FactoryDefinitionInvokableTestClass'],
            '[Class, staticMethod]' => [[__NAMESPACE__ . '\FactoryDefinitionTestClass', 'staticFoo']],
            'Class::staticMethod'   => [__NAMESPACE__ . '\FactoryDefinitionTestClass::staticFoo'],
            '[object, method]'      => [[new FactoryDefinitionTestClass, 'foo']],
            '[class, method]'       => [[__NAMESPACE__ . '\FactoryDefinitionTestClass', 'foo']],
            'class::method'         => [__NAMESPACE__ . '\FactoryDefinitionTestClass::foo'],
        ];
    }

    public function provideArbitraryNamedContainerObjectCallables()
    {
        return [
            '[arbitraryClassEntry, method]' => [['bar_baz', 'foo']],
            'arbitraryClassEntry::method'   => ['bar_baz::foo'],
        ];
    }

    public function test_simple_closure_as_factory()
    {
        $container = $this->createContainer([
            'factory' => function () {
                return 'bar';
            },
        ]);

        $factory = $container->get('factory');

        $this->assertSame('bar', $factory);
    }

    /**
     * @dataProvider provideCallables
     */
    public function test_factory_helper_function($callable)
    {
        $container = $this->createContainer([
            'factory' => \DI\factory($callable),
        ]);

        $factory = $container->get('factory');

        $this->assertSame('bar', $factory);
    }

    /**
     * @dataProvider provideArbitraryNamedContainerObjectCallables
     */
    public function test_arbitrary_named_container_object_as_factory($callable)
    {
        $container = $this->createContainer([
            'bar_baz' => \DI\object(__NAMESPACE__ . '\FactoryDefinitionTestClass'),
            'factory' => \DI\factory($callable),
        ]);

        $factory = $container->get('factory');

        $this->assertSame('bar', $factory);
    }

    public function test_arbitrary_named_invokable_container_object_as_factory()
    {
        $container = $this->createContainer([
            'bar_baz' => \DI\object(__NAMESPACE__ . '\FactoryDefinitionInvokableTestClass'),
            'factory' => \DI\factory('bar_baz'),
        ]);

        $factory = $container->get('factory');

        $this->assertSame('bar', $factory);
    }

    /**
     * @expectedException \DI\Definition\Exception\DefinitionException
     * @expectedExceptionMessage Entry "foo" cannot be resolved: factory "Hello World" is neither a callable nor a valid container entry
     */
    public function test_not_callable_factory_definition()
    {
        $container = $this->createContainer([
            'foo' => \DI\factory('Hello World'),
        ]);

        $container->get('foo');
    }

    private function createContainer(array $definitions)
    {
        $builder = new ContainerBuilder();
        $builder->addDefinitions($definitions);

        return $builder->build();
    }
}

class FactoryDefinitionTestClass
{
    public static function staticFoo()
    {
        return 'bar';
    }

    public function foo()
    {
        return 'bar';
    }
}

class FactoryDefinitionInvokableTestClass
{
    public function __invoke()
    {
        return 'bar';
    }
}

function FactoryDefinition_test()
{
    return 'bar';
}
