<?php

namespace DI\Test\IntegrationTest\Definitions;

use DI\ContainerBuilder;
use DI\Test\IntegrationTest\Definitions\AutowireDefinitionTest\NullableConstructorParameter;
use DI\Test\IntegrationTest\Definitions\AutowireDefinitionTest\NullableTypedConstructorParameter;
use DI\Test\IntegrationTest\Definitions\AutowireDefinitionTest\Setter;
use DI\Test\IntegrationTest\Definitions\AutowireDefinitionTest\TypedSetter;
use DI\Test\IntegrationTest\Definitions\ObjectDefinition\Class1;
use DI\Test\IntegrationTest\Definitions\ObjectDefinition\Class2;
use DI\Test\IntegrationTest\Definitions\ObjectDefinition\Class3;
use function DI\autowire;
use function DI\get;

/**
 * Test autowired definitions.
 *
 * @coversNothing
 */
class AutowireDefinitionTest extends BaseDefinitionTest
{
    /**
     * @dataProvider provideContainer
     */
    public function test_autowire_simple_object(ContainerBuilder $builder)
    {
        $container = $builder->addDefinitions([
            'stdClass' => autowire('stdClass'), // with the same name
            'object' => autowire(Class1::class), // with a different name
        ])->build();

        self::assertInstanceOf('stdClass', $container->get('stdClass'));
        self::assertInstanceOf(Class1::class, $container->get('object'));
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_singleton(ContainerBuilder $builder)
    {
        $container = $builder->addDefinitions([
            'stdClass' => autowire(),
        ])->build();

        self::assertSame($container->get('stdClass'), $container->get('stdClass'));
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_infer_class_name_from_entry(ContainerBuilder $builder)
    {
        $container = $builder->addDefinitions([
            Class1::class => autowire(),
        ])->build();

        self::assertInstanceOf(Class1::class, $container->get(Class1::class));
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_overrides_the_previous_entry(ContainerBuilder $builder)
    {
        $container = $builder->addDefinitions([
            'foo' => autowire(Class2::class)->property('bar', 123),
        ])->addDefinitions([
            'foo' => autowire(Class2::class)->property('bim', 456),
        ])->build();

        $foo = $container->get('foo');
        self::assertEquals(null, $foo->bar, 'The "bar" property is not set');
        self::assertEquals(456, $foo->bim, 'The "bim" property is set');
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_has_entry_when_explicitly_autowired(ContainerBuilder $builder)
    {
        $container = $builder->addDefinitions([
            Class1::class => autowire(),
        ])->build();
        self::assertTrue($container->has(Class1::class));
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_has_entry_when_not_explicitly_autowired(ContainerBuilder $builder)
    {
        self::assertTrue($builder->build()->has(Class1::class));
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_setting_specific_constructor_parameter(ContainerBuilder $builder)
    {
        $container = $builder->addDefinitions([
            NullableConstructorParameter::class => autowire()
                ->constructorParameter('bar', 'Hello'),
        ])->build();

        self::assertEquals('Hello', $container->get(NullableConstructorParameter::class)->bar);
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_setting_specific_constructor_parameter_overrides_autowiring(ContainerBuilder $builder)
    {
        $container = $builder->addDefinitions([
            NullableTypedConstructorParameter::class => autowire()
                ->constructorParameter('bar', get('foo')),
            'stdClass' => new \stdClass,
            'foo' => new \stdClass,
        ])->build();

        self::assertSame($container->get('foo'), $container->get(NullableTypedConstructorParameter::class)->bar);
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_setting_specific_method_parameter(ContainerBuilder $builder)
    {
        $container = $builder->addDefinitions([
            Setter::class => autowire()
                ->methodParameter('setFoo', 'bar', 'Hello'),
        ])->build();

        self::assertEquals('Hello', $container->get(Setter::class)->bar);
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_setting_specific_method_parameter_overrides_autowiring(ContainerBuilder $builder)
    {
        $container = $builder->addDefinitions([
            TypedSetter::class => autowire()
                ->methodParameter('setFoo', 'bar', get('foo')),
            'stdClass' => new \stdClass,
            'foo' => new \stdClass,
        ])->build();

        self::assertSame($container->get('foo'), $container->get(TypedSetter::class)->bar);
    }

    /**
     * @dataProvider provideContainer
     * @expectedException \DI\Definition\Exception\InvalidDefinition
     * @expectedExceptionMessage Cannot autowire entry "DI\Test\IntegrationTest\Definitions\ObjectDefinition\Class3" because autowiring is disabled
     */
    public function test_cannot_use_autowire_if_autowiring_is_disabled(ContainerBuilder $builder)
    {
        $container = $builder
            ->useAutowiring(false)
            ->addDefinitions([
                Class3::class => autowire(),
            ])->build();
        $container->get(Class3::class);
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_same_method_can_be_called_multiple_times(ContainerBuilder $builder)
    {
        $container = $builder
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

namespace DI\Test\IntegrationTest\Definitions\AutowireDefinitionTest;

class NullableConstructorParameter
{
    public $bar;

    public function __construct($bar = null)
    {
        $this->bar = $bar;
    }
}

class NullableTypedConstructorParameter
{
    public $bar;

    public function __construct(\stdClass $bar = null)
    {
        $this->bar = $bar;
    }
}

class Setter
{
    public $bar;

    public function setFoo($bar)
    {
        $this->bar = $bar;
    }
}

class TypedSetter
{
    public $bar;

    public function setFoo(\stdClass $bar)
    {
        $this->bar = $bar;
    }
}
