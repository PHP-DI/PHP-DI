<?php

namespace DI\Test\IntegrationTest\Definitions;

use DI\Container;
use DI\ContainerBuilder;
use DI\Test\IntegrationTest\Definitions\ObjectDefinition\Class1;
use DI\Test\IntegrationTest\Definitions\ObjectDefinition\Class2;
use DI\Test\IntegrationTest\Definitions\ObjectDefinition\Class3;
use function DI\autowire;

/**
 * Test autowired definitions.
 *
 * @coversNothing
 */
class AutowireDefinitionTest extends \PHPUnit_Framework_TestCase
{
    public function test_autowire_simple_object()
    {
        $container = (new ContainerBuilder)->addDefinitions([
            'stdClass' => autowire('stdClass'), // with the same name
            'object' => autowire(Class1::class), // with a different name
        ])->build();

        self::assertInstanceOf('stdClass', $container->get('stdClass'));
        self::assertInstanceOf(Class1::class, $container->get('object'));
    }

    public function test_infer_class_name_from_entry()
    {
        $container = (new ContainerBuilder)->addDefinitions([
            Class1::class => autowire(),
        ])->build();

        self::assertInstanceOf(Class1::class, $container->get(Class1::class));
    }

    public function test_overrides_the_previous_entry()
    {
        $container = (new ContainerBuilder)->addDefinitions([
            'foo' => autowire(Class2::class)->property('bar', 123),
        ])->addDefinitions([
            'foo' => autowire(Class2::class)->property('bim', 456),
        ])->build();

        $foo = $container->get('foo');
        self::assertEquals(null, $foo->bar, 'The "bar" property is not set');
        self::assertEquals(456, $foo->bim, 'The "bim" property is set');
    }

    public function test_has_entry_when_explicitly_autowired()
    {
        $container = (new ContainerBuilder)->addDefinitions([
            Class1::class => autowire(),
        ])->build();
        self::assertTrue($container->has(Class1::class));
    }

    public function test_has_entry_when_not_explicitly_autowired()
    {
        self::assertTrue((new Container)->has(Class1::class));
    }

    public function test_setting_specific_constructor_parameter()
    {
        $class = get_class(new class() {
            public $bar;

            public function __construct($bar = null)
            {
                $this->bar = $bar;
            }
        });

        $container = (new ContainerBuilder)->addDefinitions([
            $class => autowire()
                ->constructorParameter('bar', 'Hello'),
        ])->build();

        self::assertEquals('Hello', $container->get($class)->bar);
    }

    public function test_setting_specific_constructor_parameter_overrides_autowiring()
    {
        $class = get_class(new class() {
            public $bar;

            public function __construct(\stdClass $bar = null)
            {
                $this->bar = $bar;
            }
        });

        $expectedInstance = new \stdClass;

        $container = (new ContainerBuilder)->addDefinitions([
            $class => autowire()
                ->constructorParameter('bar', $expectedInstance),
            'stdClass' => new \stdClass,
        ])->build();

        self::assertSame($expectedInstance, $container->get($class)->bar);
    }

    public function test_setting_specific_method_parameter()
    {
        $class = get_class(new class() {
            public $bar;

            public function setFoo($bar)
            {
                $this->bar = $bar;
            }
        });

        $container = (new ContainerBuilder)->addDefinitions([
            $class => autowire()
                ->methodParameter('setFoo', 'bar', 'Hello'),
        ])->build();

        self::assertEquals('Hello', $container->get($class)->bar);
    }

    public function test_setting_specific_method_parameter_overrides_autowiring()
    {
        $class = get_class(new class() {
            public $bar;

            public function setFoo(\stdClass $bar)
            {
                $this->bar = $bar;
            }
        });

        $expectedInstance = new \stdClass;

        $container = (new ContainerBuilder)->addDefinitions([
            $class => autowire()
                ->methodParameter('setFoo', 'bar', $expectedInstance),
            'stdClass' => new \stdClass,
        ])->build();

        self::assertSame($expectedInstance, $container->get($class)->bar);
    }

    /**
     * @expectedException \DI\Definition\Exception\InvalidDefinition
     * @expectedExceptionMessage Cannot autowire entry "DI\Test\IntegrationTest\Definitions\ObjectDefinition\Class3" because autowiring is disabled
     */
    public function test_cannot_use_autowire_if_autowiring_is_disabled()
    {
        $container = (new ContainerBuilder)
            ->useAutowiring(false)
            ->addDefinitions([
                Class3::class => autowire(),
            ])->build();
        $container->get(Class3::class);
    }

    public function test_same_method_can_be_called_multiple_times()
    {
        $container = (new ContainerBuilder)
            ->useAutowiring(true)
            ->addDefinitions([
                Class1::class => autowire()
                    ->method('increment')
                    ->method('increment'),
            ])->build();

        $class = $container->get(Class1::class);
        $this->assertEquals(2, $class->count);
    }
}
