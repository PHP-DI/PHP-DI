<?php

declare(strict_types=1);

namespace DI\Test\IntegrationTest\Definitions;

use DI\ContainerBuilder;
use DI\Test\IntegrationTest\BaseContainerTest;
use DI\Test\IntegrationTest\Definitions\AutowireDefinition\Php71;
use DI\Test\IntegrationTest\Definitions\AutowireDefinitionTest\ConstructorInjection;
use DI\Test\IntegrationTest\Definitions\AutowireDefinitionTest\LazyService;
use DI\Test\IntegrationTest\Definitions\AutowireDefinitionTest\NullableConstructorParameter;
use DI\Test\IntegrationTest\Definitions\AutowireDefinitionTest\NullableTypedConstructorParameter;
use DI\Test\IntegrationTest\Definitions\AutowireDefinitionTest\Setter;
use DI\Test\IntegrationTest\Definitions\AutowireDefinitionTest\TypedSetter;
use DI\Test\IntegrationTest\Definitions\ObjectDefinition\A;
use DI\Test\IntegrationTest\Definitions\ObjectDefinition\B;
use DI\Test\IntegrationTest\Definitions\ObjectDefinition\C;
use DI\Test\IntegrationTest\Definitions\ObjectDefinition\Class1;
use DI\Test\IntegrationTest\Definitions\ObjectDefinition\Class2;
use DI\Test\IntegrationTest\Definitions\ObjectDefinition\Class3;
use DI\Test\IntegrationTest\Definitions\ObjectDefinition\VariadicParameterInConstructor;
use DI\Test\IntegrationTest\Definitions\ObjectDefinition\VariadicParameterInMethod;
use ProxyManager\Proxy\LazyLoadingInterface;
use function DI\autowire;
use function DI\create;
use function DI\get;
use DI\Definition\Exception\InvalidDefinition;

/**
 * Test autowired definitions.
 */
class AutowireDefinitionTest extends BaseContainerTest
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

        self::assertEntryIsCompiled($container, 'stdClass');
        self::assertInstanceOf('stdClass', $container->get('stdClass'));
        self::assertEntryIsCompiled($container, 'object');
        self::assertInstanceOf(Class1::class, $container->get('object'));
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_constructor_injection(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            ConstructorInjection::class => autowire()
                ->constructorParameter('value', 'bar'),
            'foo' => 'bar',
            LazyService::class => autowire()->lazy(),
        ]);
        $container = $builder->build();

        $object = $container->get(ConstructorInjection::class);

        self::assertEquals(new \stdClass, $object->typedValue);
        self::assertEquals(new \stdClass, $object->typedOptionalValue);
        self::assertEquals('bar', $object->value);
        self::assertInstanceOf(LazyService::class, $object->lazyService);
        self::assertInstanceOf(LazyLoadingInterface::class, $object->lazyService);
        self::assertFalse($object->lazyService->isProxyInitialized());
        self::assertNull($object->unknownTypedAndOptional);
        self::assertEquals('hello', $object->optionalValue);
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_constructor_injection_with_named_arguments(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            ConstructorInjection::class => autowire()
                ->constructor(
                    value: 'bar',
                ),
        ]);
        $container = $builder->build();

        $object = $container->get(ConstructorInjection::class);
        self::assertEquals('bar', $object->value);
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_autowired_constructor_injection_can_be_overloaded(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            AutowireDefinition\ConstructorInjection::class => autowire()
                ->constructorParameter('overloadedParameter', get('foo')),

            AutowireDefinition\Class1::class => create(),
            'foo' => create(AutowireDefinition\Class1::class),
        ]);
        $container = $builder->build();

        $object = $container->get(AutowireDefinition\ConstructorInjection::class);

        self::assertSame($container->get(\stdClass::class), $object->autowiredParameter);

        self::assertSame($container->get('foo'), $object->overloadedParameter);
        self::assertNotSame($container->get(AutowireDefinition\Class1::class), $object->overloadedParameter);
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_annotated_constructor_injection_can_be_overloaded(ContainerBuilder $builder)
    {
        $builder->useAttributes(true);
        $builder->addDefinitions([
            AutowireDefinition\ConstructorInjection::class => autowire()
                ->constructorParameter('overloadedParameter', get('foo')),

            \stdClass::class => create(),
            'anotherStdClass' => create(\stdClass::class),

            AutowireDefinition\Class1::class => create(),
            'foo' => create(AutowireDefinition\Class1::class),
        ]);
        $container = $builder->build();

        $object = $container->get(AutowireDefinition\ConstructorInjection::class);

        self::assertSame($container->get('anotherStdClass'), $object->autowiredParameter);
        self::assertNotSame($container->get(\stdClass::class), $object->autowiredParameter);

        self::assertSame($container->get('foo'), $object->overloadedParameter);
        self::assertNotSame($container->get(AutowireDefinition\Class1::class), $object->overloadedParameter);
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_annotated_method_injection_can_be_overloaded(ContainerBuilder $builder)
    {
        $builder->useAttributes(true);
        $builder->addDefinitions([
            AutowireDefinition\MethodInjection::class => autowire()
                ->methodParameter('setFoo', 'overloadedParameter', get('foo')),

            \stdClass::class => create(),
            'anotherStdClass' => create(\stdClass::class),

            AutowireDefinition\Class1::class => create(),
            'foo' => create(AutowireDefinition\Class1::class),
        ]);
        $container = $builder->build();

        $object = $container->get(AutowireDefinition\MethodInjection::class);

        self::assertSame($container->get('anotherStdClass'), $object->autowiredParameter);
        self::assertNotSame($container->get(\stdClass::class), $object->autowiredParameter);

        self::assertSame($container->get('foo'), $object->overloadedParameter);
        self::assertNotSame($container->get(AutowireDefinition\Class1::class), $object->overloadedParameter);
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

        self::assertEntryIsCompiled($container, 'foo');
        self::assertNull($foo->bar, 'The "bar" property is not set');
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
    public function test_setting_specific_method_parameter_with_named_arguments(ContainerBuilder $builder)
    {
        $container = $builder->addDefinitions([
            Setter::class => autowire()
                ->method('setFoo', bar: 'Hello'),
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
     */
    public function test_cannot_use_autowire_if_autowiring_is_disabled(ContainerBuilder $builder)
    {
        $this->expectException(InvalidDefinition::class);
        $this->expectExceptionMessage('Cannot autowire entry "DI\Test\IntegrationTest\Definitions\ObjectDefinition\Class3" because autowiring is disabled');
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

    /**
     * @dataProvider provideContainer
     */
    public function test_autowire_lazy_object(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            NullableConstructorParameter::class => autowire()
                ->property('bar', 'bar')
                ->lazy(),
        ]);
        $container = $builder->build();

        $object = $container->get(NullableConstructorParameter::class);

        $this->assertEntryIsCompiled($container, NullableConstructorParameter::class);
        self::assertInstanceOf(NullableConstructorParameter::class, $object);
        self::assertInstanceOf(LazyLoadingInterface::class, $object);
        self::assertFalse($object->isProxyInitialized());
        self::assertEquals('bar', $object->bar);
        self::assertTrue($object->isProxyInitialized());
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_php71_nullable_typehint(ContainerBuilder $builder)
    {
        $container = $builder->build();

        $object = $container->get(Php71::class);

        self::assertEquals(new \stdClass, $object->param);
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_with_variadics_positional(ContainerBuilder $builder)
    {
        $builder
            ->addDefinitions([
                'foo' => autowire(VariadicParameterInConstructor::class)
                    ->constructorParameter(1, get(B::class)),
                'bar' => autowire(VariadicParameterInMethod::class)
                    ->methodParameter('method1', 1, get(B::class))
                    ->methodParameter('method2', 2, get(C::class)),
            ]);

        $container = $builder->build();

        $foo = $container->get('foo');
        self::assertInstanceOf(VariadicParameterInConstructor::class, $foo);
        self::assertNotEmpty($foo->a);
        self::assertNotEmpty($foo->b);
        self::assertEmpty($foo->c);

        $bar = $container->get('bar');
        self::assertInstanceOf(VariadicParameterInMethod::class, $bar);
        self::assertNotEmpty($bar->a1);
        self::assertNotEmpty($bar->b1);
        self::assertEmpty($bar->c1);
        self::assertNotEmpty($bar->a2);
        self::assertNotEmpty($bar->b2);
        self::assertNotEmpty($bar->c2);
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_with_variadics_named(ContainerBuilder $builder)
    {
        $builder
            ->addDefinitions([
                'foo' => autowire(VariadicParameterInConstructor::class)
                    ->constructorParameter('v', get(B::class)),
                'bar' => autowire(VariadicParameterInMethod::class)
                    ->methodParameter('method1', 'v', get(B::class))
                    ->methodParameter('method2', 'v', get(C::class)),
            ]);

        $container = $builder->build();

        $foo = $container->get('foo');
        self::assertInstanceOf(VariadicParameterInConstructor::class, $foo);
        self::assertNotEmpty($foo->a);
        self::assertNotEmpty($foo->b);
        self::assertEmpty($foo->c);

        $bar = $container->get('bar');
        self::assertInstanceOf(VariadicParameterInMethod::class, $bar);
        self::assertNotEmpty($bar->a1);
        self::assertNotEmpty($bar->b1);
        self::assertEmpty($bar->c1);
        self::assertNotEmpty($bar->a2);
        self::assertNotEmpty($bar->b2);
        self::assertNotEmpty($bar->c2);
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_with_variadics_empty(ContainerBuilder $builder)
    {
        $builder
            ->addDefinitions([
                'foo' => autowire(VariadicParameterInConstructor::class),
                'bar' => autowire(VariadicParameterInMethod::class)
                    ->methodParameter('method1', 'a', get(A::class))
                    ->methodParameter('method2', 'b', get(B::class)),
            ]);

        $container = $builder->build();

        $foo = $container->get('foo');
        self::assertInstanceOf(VariadicParameterInConstructor::class, $foo);
        self::assertNotEmpty($foo->a);
        self::assertEmpty($foo->b);
        self::assertEmpty($foo->c);

        $bar = $container->get('bar');
        self::assertInstanceOf(VariadicParameterInMethod::class, $bar);
        self::assertNotEmpty($bar->a1);
        self::assertEmpty($bar->b1);
        self::assertEmpty($bar->c1);
        self::assertNotEmpty($bar->a2);
        self::assertNotEmpty($bar->b2);
        self::assertEmpty($bar->c2);
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

class ConstructorInjection
{
    public $value;
    public $typedValue;
    public $typedOptionalValue;
    /** @var \ProxyManager\Proxy\LazyLoadingInterface */
    public $lazyService;
    public $unknownTypedAndOptional;
    public $optionalValue;

    public function __construct(
        \stdClass $typedValue,
        string $value,
        \stdClass $typedOptionalValue = null,
        LazyService $lazyService,
        UnknownClass $unknownTypedAndOptional = null,
        $optionalValue = 'hello'
    ) {
        $this->value = $value;
        $this->typedValue = $typedValue;
        $this->typedOptionalValue = $typedOptionalValue;
        $this->lazyService = $lazyService;
        $this->unknownTypedAndOptional = $unknownTypedAndOptional;
        $this->optionalValue = $optionalValue;
    }
}

class LazyService
{
}
