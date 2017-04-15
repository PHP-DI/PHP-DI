<?php

namespace DI\Test\IntegrationTest\Definitions;

use DI\ContainerBuilder;
use DI\Factory\RequestedEntry;
use DI\Test\UnitTest\Definition\Resolver\Fixture\NoConstructor;
use Psr\Container\ContainerInterface;

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

    /**
     * @expectedException \DI\Definition\Exception\InvalidDefinition
     * @expectedExceptionMessage Invokable classes cannot be automatically resolved if autowiring is disabled on the container, you need to enable autowiring or define the entry manually.
     */
    public function test_error_message_on_invokable_class_without_autowiring()
    {
        $builder = new ContainerBuilder();
        $builder->addDefinitions([
            'factory' => \DI\factory(FactoryDefinitionInvokableTestClass::class),
        ]);
        $builder->useAutowiring(false);
        $container = $builder->build();
        $container->get('factory');
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

    public function test_interop_container_get_injected_in_arbitrary_position_via_typehint()
    {
        $container = $this->createContainer([
            'factory' => function (\stdClass $stdClass, \Interop\Container\ContainerInterface $c) {
                return [$stdClass, $c];
            },
        ]);

        $factory = $container->get('factory');

        $this->assertInstanceOf('stdClass', $factory[0]);
        $this->assertInstanceOf(ContainerInterface::class, $factory[1]);
    }

    public function test_value_gets_injected_via_parameter()
    {
        $container = $this->createContainer([
            'factory' => \DI\factory(function ($value) {
                return $value;
            })->parameter('value', 'Foo'),
        ]);

        $factory = $container->get('factory');

        $this->assertEquals('Foo', $factory);
    }

    public function test_named_entry_gets_injected_via_parameter()
    {
        $container = $this->createContainer([
            'basicClass' => \DI\object(\stdClass::class),
            'factory' => \DI\factory(function ($entry) {
                return $entry;
            })->parameter('entry', \DI\get('basicClass')),
        ]);

        $factory = $container->get('factory');

        $this->assertInstanceOf(\stdClass::class, $factory);
    }

    public function test_sub_entry_gets_injected_via_parameter()
    {
        $container = $this->createContainer([
            'factory' => \DI\factory(function ($entry) {
                return $entry;
            })->parameter('entry', \DI\object(\stdClass::class)),
        ]);

        $factory = $container->get('factory');

        $this->assertInstanceOf(\stdClass::class, $factory);
    }

    public function test_class_gets_injected_via_parameter()
    {
        $container = $this->createContainer([
            'factory' => \DI\factory(function ($entry) {
                return $entry;
            })->parameter('entry', \DI\get(\stdClass::class)),
        ]);

        $factory = $container->get('factory');

        $this->assertInstanceOf(\stdClass::class, $factory);
    }

    public function test_multiple_injections_via_parameter()
    {
        $container = $this->createContainer([
            'secret' => 'Bar',
            'factory' => \DI\factory(function ($a, $b, $c) {
                return [$a, $b, $c];
            })->parameter('a', \DI\get('secret'))
              ->parameter('b', \DI\object(FactoryDefinitionTestClass::class))
              ->parameter('c', 'Foo'),
        ]);

        $factory = $container->get('factory');

        $this->assertEquals('Bar', $factory[0]);
        $this->assertInstanceOf(FactoryDefinitionTestClass::class, $factory[1]);
        $this->assertEquals('Foo', $factory[2]);
    }

    public function test_container_and_requested_entry_and_typehints_get_injected_with_parameter()
    {
        $container = $this->createContainer([
            'secret' => 'Bar',
            'factory' => \DI\factory(function ($container, $requestedEntry, \stdClass $object, $value) {
                return [$container, $requestedEntry, $object, $value];
            })->parameter('value', \DI\get('secret')),
        ]);

        $factory = $container->get('factory');

        $this->assertInstanceOf(ContainerInterface::class, $factory[0]);
        $this->assertInstanceOf(RequestedEntry::class, $factory[1]);
        $this->assertInstanceOf('stdClass', $factory[2]);
        $this->assertEquals('Bar', $factory[3]);
    }

    public function test_container_and_requested_entry_and_typehints_get_injected_in_arbitrary_positions_with_parameter()
    {
        $container = $this->createContainer([
            'secret' => 'Bar',
            'factory' => \DI\factory(function (\stdClass $object, RequestedEntry $requestedEntry, $value, ContainerInterface $container) {
                return [$object, $requestedEntry, $value, $container];
            })->parameter('value', \DI\get('secret')),
        ]);

        $factory = $container->get('factory');

        $this->assertInstanceOf('stdClass', $factory[0]);
        $this->assertInstanceOf(RequestedEntry::class, $factory[1]);
        $this->assertEquals('Bar', $factory[2]);
        $this->assertInstanceOf(ContainerInterface::class, $factory[3]);
    }

    public function test_parameters_take_priority_over_container()
    {
        $ncInst = new NoConstructor();

        $container = $this->createContainer([
            'factory' => \DI\factory(function (NoConstructor $nc) {
                return $nc;
            })->parameter('nc', $ncInst),
        ]);

        $factory = $container->get('factory');

        $this->assertSame($ncInst, $factory);
    }

    public function test_parameters_take_priority_over_default_value()
    {
        $container = $this->createContainer([
            'factory' => \DI\factory(function ($foo = 'Foo') {
                return $foo;
            })->parameter('foo', 'Bar'),
        ]);

        $factory = $container->get('factory');

        $this->assertEquals('Bar', $factory);
    }

    /**
     * @expectedException \DI\NotFoundException
     * @expectedExceptionMessage No entry or class found for 'missing'
     */
    public function test_resolve_failure_on_parameter()
    {
        $container = $this->createContainer([
            'factory' => \DI\factory(function ($foo) {
                return $foo;
            })->parameter('foo', \DI\get('missing')),
        ]);
        $container->get('factory');
    }

    /**
     * @expectedException \DI\Definition\Exception\InvalidDefinition
     * @expectedExceptionMessage Entry "foo" cannot be resolved: factory 'Hello World' is neither a callable nor a valid container entry
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
