<?php

declare(strict_types=1);

namespace DI\Test\IntegrationTest\Definitions;

use DI\ContainerBuilder;
use DI\Factory\RequestedEntry;
use DI\Test\IntegrationTest\BaseContainerTest;
use DI\Test\UnitTest\Definition\Resolver\Fixture\NoConstructor;
use Psr\Container\ContainerInterface;
use function DI\factory;

/**
 * Test factory definitions.
 */
class FactoryDefinitionTest extends BaseContainerTest
{
    /**
     * @dataProvider provideContainer
     */
    public function test_closure_shortcut(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            'factory' => function () {
                return 'bar';
            },
        ]);

        $container = $builder->build();
        self::assertEntryIsCompiled($container, 'factory');
        $this->assertEquals('bar', $container->get('factory'));
    }

    public function provideCallables()
    {
        $callables = [
            'closure' => function () {
                return 'bar';
            },
            'function' => __NAMESPACE__ . '\FactoryDefinition_test',
            'invokableObject' => new FactoryDefinitionInvokableTestClass,
            'invokableClass' => FactoryDefinitionInvokableTestClass::class,
            '[Class, staticMethod]' => [FactoryDefinitionTestClass::class, 'staticFoo'],
            'Class::staticMethod' => FactoryDefinitionTestClass::class . '::staticFoo',
            '[object, method]' => [new FactoryDefinitionTestClass, 'foo'],
            '[class, method]' => [FactoryDefinitionTestClass::class, 'foo'],
            'class::method' => FactoryDefinitionTestClass::class . '::foo',
        ];

        $testCases = [];
        foreach ($callables as $callableName => $callable) {
            foreach ($this->provideContainer() as $containerName => $container) {
                $testCases[$containerName . ' - ' . $callableName] = [$callable, clone $container[0]];
            }
        }

        return $testCases;
    }

    /**
     * @dataProvider provideCallables
     */
    public function test_factory($callable, ContainerBuilder $builder)
    {
        $isClosure = $callable instanceof \Closure;
        $containsAnObject = is_object($callable) || is_object($callable[0]);
        if ($builder->isCompilationEnabled() && $containsAnObject && !$isClosure) {
            // Invokable objects are not compilable
            $this->expectException(\DI\Definition\Exception\InvalidDefinition::class);
            $this->expectExceptionMessage('An object was found but objects cannot be compiled');
        }

        $builder->addDefinitions([
            'factory' => \DI\factory($callable),
        ]);

        $container = $builder->build();
        self::assertEntryIsCompiled($container, 'factory');
        self::assertSame('bar', $container->get('factory'));
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_named_container_entry_as_factory(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            'bar_baz' => \DI\create(FactoryDefinitionTestClass::class),
            'factory' => \DI\factory(['bar_baz', 'foo']),
        ]);

        $container = $builder->build();
        self::assertEntryIsCompiled($container, 'factory');
        self::assertSame('bar', $container->get('factory'));
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_named_container_entry_as_factory_with_string_callable(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            'bar_baz' => \DI\create(FactoryDefinitionTestClass::class),
            'factory' => \DI\factory('bar_baz::foo'),
        ]);

        $container = $builder->build();
        self::assertEntryIsCompiled($container, 'factory');
        self::assertSame('bar', $container->get('factory'));
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_named_invokable_container_entry_as_factory(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            'bar_baz' => \DI\create(FactoryDefinitionInvokableTestClass::class),
            'factory' => \DI\factory('bar_baz'),
        ]);

        $container = $builder->build();
        self::assertEntryIsCompiled($container, 'factory');
        self::assertSame('bar', $container->get('factory'));
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_error_message_on_invokable_class_without_autowiring(ContainerBuilder $builder)
    {
        $this->expectException('DI\Definition\Exception\InvalidDefinition');
        $this->expectExceptionMessage('Invokable classes cannot be automatically resolved if autowiring is disabled on the container, you need to enable autowiring or define the entry manually.');
        $builder->addDefinitions([
            'factory' => \DI\factory(FactoryDefinitionInvokableTestClass::class),
        ]);
        $builder->useAutowiring(false);
        $container = $builder->build();
        $container->get('factory');
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_container_gets_injected_as_first_argument_without_typehint(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            'factory' => function ($c) {
                return $c;
            },
        ]);

        $factory = $builder->build()->get('factory');

        $this->assertInstanceOf(ContainerInterface::class, $factory);
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_requested_entry_gets_injected_as_second_argument_without_typehint(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            'foobar' => function ($c, $entry) {
                return $entry->getName();
            },
        ]);

        $factory = $builder->build()->get('foobar');

        $this->assertSame('foobar', $factory);
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_requested_entry_gets_injected_with_typehint(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            'foobar' => function (RequestedEntry $entry) {
                return $entry->getName();
            },
        ]);

        $factory = $builder->build()->get('foobar');

        $this->assertSame('foobar', $factory);
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_arbitrary_object_gets_injected_via_typehint(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            'factory' => function (\stdClass $stdClass) {
                return $stdClass;
            },
        ]);

        $factory = $builder->build()->get('factory');

        $this->assertInstanceOf('stdClass', $factory);
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_container_and_requested_entry_get_injected_in_arbitrary_position_via_typehint(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            'factory' => function (\stdClass $stdClass, RequestedEntry $e, ContainerInterface $c) {
                return [$stdClass, $e, $c];
            },
        ]);

        $factory = $builder->build()->get('factory');

        $this->assertInstanceOf('stdClass', $factory[0]);
        $this->assertInstanceOf(RequestedEntry::class, $factory[1]);
        $this->assertInstanceOf(ContainerInterface::class, $factory[2]);
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_container_get_injected_in_arbitrary_position_via_typehint(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            'factory' => function (\stdClass $stdClass, ContainerInterface $c) {
                return [$stdClass, $c];
            },
        ]);

        $factory = $builder->build()->get('factory');

        $this->assertInstanceOf('stdClass', $factory[0]);
        $this->assertInstanceOf(ContainerInterface::class, $factory[1]);
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_value_gets_injected_via_parameter(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            'factory' => \DI\factory(function ($value) {
                return $value;
            })->parameter('value', 'Foo'),
        ]);

        $factory = $builder->build()->get('factory');

        $this->assertEquals('Foo', $factory);
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_named_entry_gets_injected_via_parameter(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            'basicClass' => \DI\create(\stdClass::class),
            'factory' => \DI\factory(function ($entry) {
                return $entry;
            })->parameter('entry', \DI\get('basicClass')),
        ]);

        $factory = $builder->build()->get('factory');

        $this->assertInstanceOf(\stdClass::class, $factory);
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_sub_entry_gets_injected_via_parameter(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            'factory' => \DI\factory(function ($entry) {
                return $entry;
            })->parameter('entry', \DI\create(\stdClass::class)),
        ]);

        $factory = $builder->build()->get('factory');

        $this->assertInstanceOf(\stdClass::class, $factory);
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_class_gets_injected_via_parameter(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            'factory' => \DI\factory(function ($entry) {
                return $entry;
            })->parameter('entry', \DI\get(\stdClass::class)),
        ]);

        $factory = $builder->build()->get('factory');

        $this->assertInstanceOf(\stdClass::class, $factory);
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_multiple_injections_via_parameter(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            'secret' => 'Bar',
            'factory' => \DI\factory(function ($a, $b, $c) {
                return [$a, $b, $c];
            })->parameter('a', \DI\get('secret'))
              ->parameter('b', \DI\create(FactoryDefinitionTestClass::class))
              ->parameter('c', 'Foo'),
        ]);

        $factory = $builder->build()->get('factory');

        $this->assertEquals('Bar', $factory[0]);
        $this->assertInstanceOf(FactoryDefinitionTestClass::class, $factory[1]);
        $this->assertEquals('Foo', $factory[2]);
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_container_and_requested_entry_and_typehints_get_injected_with_parameter(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            'secret' => 'Bar',
            'factory' => \DI\factory(function ($container, $requestedEntry, \stdClass $object, $value) {
                return [$container, $requestedEntry, $object, $value];
            })->parameter('value', \DI\get('secret')),
        ]);

        $factory = $builder->build()->get('factory');

        $this->assertInstanceOf(ContainerInterface::class, $factory[0]);
        $this->assertInstanceOf(RequestedEntry::class, $factory[1]);
        $this->assertInstanceOf('stdClass', $factory[2]);
        $this->assertEquals('Bar', $factory[3]);
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_container_and_requested_entry_and_typehints_get_injected_in_arbitrary_positions_with_parameter(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            'secret' => 'Bar',
            'factory' => \DI\factory(function (\stdClass $object, RequestedEntry $requestedEntry, $value, ContainerInterface $container) {
                return [$object, $requestedEntry, $value, $container];
            })->parameter('value', \DI\get('secret')),
        ]);

        $factory = $builder->build()->get('factory');

        $this->assertInstanceOf('stdClass', $factory[0]);
        $this->assertInstanceOf(RequestedEntry::class, $factory[1]);
        $this->assertEquals('Bar', $factory[2]);
        $this->assertInstanceOf(ContainerInterface::class, $factory[3]);
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_parameters_take_priority_over_container(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            'factory' => \DI\factory(function (NoConstructor $nc) {
                return $nc;
            })->parameter('nc', \DI\get('foo')),
            NoConstructor::class => \DI\autowire(),
            'foo' => \DI\autowire(NoConstructor::class),
        ]);
        $container = $builder->build();

        $parameterPassed = $container->get('factory');

        // Check that "foo" is injected, not the "NoConstructor" entry that could be autowired from the typehint
        $this->assertSame($container->get('foo'), $parameterPassed);
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_parameters_take_priority_over_default_value(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            'factory' => \DI\factory(function ($foo = 'Foo') {
                return $foo;
            })->parameter('foo', 'Bar'),
        ]);

        $factory = $builder->build()->get('factory');

        $this->assertEquals('Bar', $factory);
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_resolve_failure_on_parameter(ContainerBuilder $builder)
    {
        $this->expectException('DI\NotFoundException');
        $this->expectExceptionMessage('No entry or class found for \'missing\'');
        $builder->addDefinitions([
            'factory' => \DI\factory(function ($foo) {
                return $foo;
            })->parameter('foo', \DI\get('missing')),
        ]);
        $builder->build()->get('factory');
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_not_callable_factory_definition(ContainerBuilder $builder)
    {
        $this->expectException('DI\Definition\Exception\InvalidDefinition');
        $this->expectExceptionMessage('Entry "foo" cannot be resolved: factory \'Hello World\' is neither a callable nor a valid container entry');
        $builder->addDefinitions([
            'foo' => \DI\factory('Hello World'),
        ]);
        $builder->build()->get('foo');
    }

    /**
     * Test that __FILE__ and similar magic constants are preserved even when
     * the container is compiled.
     * @dataProvider provideContainer
     */
    public function test_closure_using_magic_constant(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            'factory' => function () {
                return __FILE__;
            },
        ]);
        $this->assertEquals(__FILE__, $builder->build()->get('factory'));
    }

    /**
     * Test that non FQN for classes are preserved even when the container is compiled.
     * @dataProvider provideContainer
     */
    public function test_closure_containing_class_name_not_fully_qualified(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            'factory' => function () {
                return FactoryDefinitionTestClass::class;
            },
        ]);
        $this->assertEquals('DI\Test\IntegrationTest\Definitions\FactoryDefinitionTestClass', $builder->build()->get('factory'));
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_closure_with_return_types_are_supported(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            'factory' => function () : \stdClass {
                return new \stdClass;
            },
        ]);
        $this->assertEquals(new \stdClass, $builder->build()->get('factory'));
    }

    public function test_closure_which_use_variables_cannot_be_compiled()
    {
        $this->expectException('DI\Definition\Exception\InvalidDefinition');
        $this->expectExceptionMessage('Cannot compile closures which import variables using the `use` keyword');
        $builder = (new ContainerBuilder)->enableCompilation(self::COMPILATION_DIR, self::generateCompiledClassName());
        $foo = 'hello';
        $builder->addDefinitions([
            'factory' => function () use ($foo) {
                return $foo;
            },
        ]);
        $builder->build();
    }

    /**
     * TODO would be better to have an error at compilation.
     */
    public function test_closure_which_use_this_cannot_be_compiled()
    {
        $this->expectException('Error');
        $this->expectExceptionMessage('Using $this when not in object context');
        $builder = (new ContainerBuilder)->enableCompilation(self::COMPILATION_DIR, self::generateCompiledClassName());
        $builder->addDefinitions([
            'factory' => function () {
                return $this->foo();
            },
        ]);
        $builder->build()->get('factory');
    }

    private function foo()
    {
        return 'hello';
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_static_closure_are_supported(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            'factory' => static function () {
                return new \stdClass;
            },
        ]);
        $container = $builder->build();

        self::assertEntryIsCompiled($container, 'factory');
        self::assertEquals(new \stdClass, $container->get('factory'));
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_closure_with_static_variables_are_supported(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            'factory' => function () {
                static $i = 0;

                return $i;
            },
        ]);
        $container = $builder->build();

        self::assertEntryIsCompiled($container, 'factory');
        self::assertSame(0, $container->get('factory'));
    }

    public function test_multiple_closures_on_the_same_line_cannot_be_compiled()
    {
        $this->expectException('DI\Definition\Exception\InvalidDefinition');
        $this->expectExceptionMessage('Cannot compile closures when two closures are defined on the same line');
        $builder = (new ContainerBuilder)->enableCompilation(self::COMPILATION_DIR, self::generateCompiledClassName());
        $builder->addDefinitions(__DIR__ . '/FactoryDefinition/config.inc');
        $this->assertEquals('foo', $builder->build()->get('factory'));
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_optional_parameters_can_be_omitted(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            'factory' => function ($c, $entry, $a = 'foo') {
                return $a;
            },
        ]);
        $container = $builder->build();

        self::assertEquals('foo', $container->get('factory'));
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_issue_628(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            'factory' => factory(function ($foo, $bar = 123, $baz = 456) {
                return [$foo, $bar, $baz];
            })->parameter('foo', 'bar'),
        ]);
        $container = $builder->build();

        self::assertEquals(['bar', 123, 456], $container->get('factory'));
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
