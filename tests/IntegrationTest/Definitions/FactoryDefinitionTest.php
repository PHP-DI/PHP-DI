<?php

namespace DI\Test\IntegrationTest\Definitions;

use DI\ContainerBuilder;
use DI\Factory\RequestedEntry;
use Interop\Container\ContainerInterface;

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
            'closure'               => [function () {
                return 'bar';
            }],
            'function'              => [__NAMESPACE__ . '\FactoryDefinition_test'],
            'invokableObject'       => [new FactoryDefinitionInvokableTestClass],
            'invokableClass'        => [FactoryDefinitionInvokableTestClass::class],
            '[Class, staticMethod]' => [[FactoryDefinitionTestClass::class, 'staticFoo']],
            'Class::staticMethod'   => [FactoryDefinitionTestClass::class . '::staticFoo'],
            '[object, method]'      => [[new FactoryDefinitionTestClass, 'foo']],
            '[class, method]'       => [[FactoryDefinitionTestClass::class, 'foo']],
            'class::method'         => [FactoryDefinitionTestClass::class . '::foo'],
        ];
    }

    public function provideNamedContainerEntryCallables()
    {
        return [
            '[arbitraryClassEntry, method]' => [['bar_baz', 'foo']],
            'arbitraryClassEntry::method'   => ['bar_baz::foo'],
        ];
    }

    public function test_closure_shortcut()
    {
        $container = $this->createContainer([
            'factory' => function () {
                return 'bar';
            },
        ]);

        $this->assertEquals('bar', $container->get('factory'));
    }

    /**
     * @dataProvider provideCallables
     */
    public function test_factory($callable)
    {
        $container = $this->createContainer([
            'factory' => \DI\factory($callable),
        ]);

        $this->assertSame('bar', $container->get('factory'));
    }

    /**
     * @dataProvider provideNamedContainerEntryCallables
     */
    public function test_named_container_entry_as_factory($callable)
    {
        $container = $this->createContainer([
            'bar_baz' => \DI\object(FactoryDefinitionTestClass::class),
            'factory' => \DI\factory($callable),
        ]);

        $this->assertSame('bar', $container->get('factory'));
    }

    public function test_named_invokable_container_entry_as_factory()
    {
        $container = $this->createContainer([
            'bar_baz' => \DI\object(FactoryDefinitionInvokableTestClass::class),
            'factory' => \DI\factory('bar_baz'),
        ]);

        $this->assertSame('bar', $container->get('factory'));
    }

    public function test_container_gets_injected_as_first_argument_without_typehint()
    {
        $container = $this->createContainer([
            'factory' => function ($c) {
                return $c;
            },
        ]);

        $factory = $container->get('factory');

        $this->assertInstanceOf(ContainerInterface::class, $factory);
    }

    public function test_requested_entry_gets_injected_as_second_argument_without_typehint()
    {
        $container = $this->createContainer([
            'foobar' => function ($c, $entry) {
                return $entry->getName();
            },
        ]);

        $factory = $container->get('foobar');

        $this->assertSame('foobar', $factory);
    }

    public function test_requested_entry_gets_injected_with_typehint()
    {
        $container = $this->createContainer([
            'foobar' => function (RequestedEntry $entry) {
                return $entry->getName();
            },
        ]);

        $factory = $container->get('foobar');

        $this->assertSame('foobar', $factory);
    }

    public function test_arbitrary_object_gets_injected_via_typehint()
    {
        $container = $this->createContainer([
            'factory' => function (\stdClass $stdClass) {
                return $stdClass;
            },
        ]);

        $factory = $container->get('factory');

        $this->assertInstanceOf('stdClass', $factory);
    }

    public function test_container_and_requested_entry_get_injected_in_arbitrary_position_via_typehint()
    {
        $container = $this->createContainer([
            'factory' => function (\stdClass $stdClass, RequestedEntry $e, ContainerInterface $c) {
                return [$stdClass, $e, $c];
            },
        ]);

        $factory = $container->get('factory');

        $this->assertInstanceOf('stdClass', $factory[0]);
        $this->assertInstanceOf(RequestedEntry::class, $factory[1]);
        $this->assertInstanceOf(ContainerInterface::class, $factory[2]);
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
