<?php

declare(strict_types=1);

namespace DI\Test\IntegrationTest\Definitions;

use DI\ContainerBuilder;
use DI\Test\IntegrationTest\BaseContainerTest;
use DI\Test\IntegrationTest\Definitions\CreateDefinitionTest\ConstructorInjection;
use DI\Test\IntegrationTest\Definitions\CreateDefinitionTest\MethodInjection;
use DI\Test\IntegrationTest\Definitions\CreateDefinitionTest\PrivatePropertyInjection;
use DI\Test\IntegrationTest\Definitions\CreateDefinitionTest\PrivatePropertyInjectionSubClass;
use DI\Test\IntegrationTest\Definitions\CreateDefinitionTest\Property;
use DI\Test\IntegrationTest\Definitions\CreateDefinitionTest\PropertyInjection;
use DI\Test\IntegrationTest\Definitions\ObjectDefinition\Class1;
use DI\Test\IntegrationTest\Definitions\ObjectDefinition\Class2;
use DI\Test\IntegrationTest\Definitions\ObjectDefinition\Class3;
use ProxyManager\Proxy\LazyLoadingInterface;
use function DI\create;
use function DI\get;

/**
 * Test object definitions.
 */
class CreateDefinitionTest extends BaseContainerTest
{
    /**
     * @dataProvider provideContainer
     */
    public function test_create_simple_object(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            // with the same name
            'stdClass' => create('stdClass'),
            // with name inferred
            Class1::class => create(),
            // with a different name
            'object' => create(Class1::class),
        ]);
        $container = $builder->build();

        $this->assertInstanceOf('stdClass', $container->get('stdClass'));
        $this->assertInstanceOf(Class1::class, $container->get(Class1::class));
        $this->assertInstanceOf(Class1::class, $container->get('object'));
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_constructor_injection(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            ConstructorInjection::class => create()
                ->constructor(
                    123,
                    get('foo'),
                    get(\stdClass::class),
                    get(\stdClass::class),
                    get('lazyService')
                ),
            'foo' => 'bar',
            'lazyService' => create(\stdClass::class)->lazy(),
        ]);
        $container = $builder->build();

        $object = $container->get(ConstructorInjection::class);

        self::assertEquals(123, $object->value);
        self::assertEquals('bar', $object->scalarValue);
        self::assertEquals(new \stdClass, $object->typedValue);
        self::assertEquals(new \stdClass, $object->typedOptionalValue);
        self::assertInstanceOf(\stdClass::class, $object->lazyService);
        self::assertInstanceOf(LazyLoadingInterface::class, $object->lazyService);
        self::assertFalse($object->lazyService->isProxyInitialized());
        self::assertEquals('hello', $object->optionalValue);
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_property_injection(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            PropertyInjection::class => create()
                // Inject value
                ->property('value', 'foo')
                // Inject other entry
                ->property('entry', get('foo'))
                // Inject lazy object
                ->property('lazyService', get('lazyService')),
            'foo' => 'bar',
            'lazyService' => create(\stdClass::class)->lazy(),
        ]);
        $container = $builder->build();

        $object = $container->get(PropertyInjection::class);

        self::assertEquals('foo', $object->value);
        self::assertEquals('bar', $object->entry);
        self::assertInstanceOf(\stdClass::class, $object->lazyService);
        self::assertInstanceOf(LazyLoadingInterface::class, $object->lazyService);
        self::assertFalse($object->lazyService->isProxyInitialized());
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_method_injection(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            MethodInjection::class => create()
                ->method(
                    'method',
                    123,
                    get('foo'),
                    get(\stdClass::class),
                    get(\stdClass::class),
                    get('lazyService')
                ),
            'foo' => 'bar',
            'lazyService' => create(\stdClass::class)->lazy(),
        ]);
        $container = $builder->build();

        $object = $container->get(MethodInjection::class);

        self::assertEquals(123, $object->value);
        self::assertEquals('bar', $object->scalarValue);
        self::assertEquals(new \stdClass, $object->typedValue);
        self::assertEquals(new \stdClass, $object->typedOptionalValue);
        self::assertInstanceOf(\stdClass::class, $object->lazyService);
        self::assertInstanceOf(LazyLoadingInterface::class, $object->lazyService);
        self::assertFalse($object->lazyService->isProxyInitialized());
        self::assertEquals('hello', $object->optionalValue);
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_singleton(ContainerBuilder $builder)
    {
        $container = $builder->addDefinitions([
            'stdClass' => create(),
        ])->build();

        self::assertSame($container->get('stdClass'), $container->get('stdClass'));
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_overrides_the_previous_entry(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            'foo' => create(Class2::class)
                ->property('bar', 123),
        ]);
        $builder->addDefinitions([
            'foo' => create(Class2::class)
                ->property('bim', 456),
        ]);
        $container = $builder->build();

        $foo = $container->get('foo');
        self::assertEquals(null, $foo->bar, 'The "bar" property is not set');
        self::assertEquals(456, $foo->bim, 'The "bim" property is set');
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_has_entry(ContainerBuilder $builder)
    {
        $container = $builder->addDefinitions([
            Class1::class => create(),
        ])->build();
        self::assertTrue($container->has(Class1::class));
    }

    /**
     * It should not inherit the definition from autowiring.
     * @dataProvider provideContainer
     * @expectedException \DI\Definition\Exception\InvalidDefinition
     * @expectedExceptionMessage Parameter $parameter of __construct() has no value defined or guessable
     */
    public function test_does_not_trigger_autowiring(ContainerBuilder $builder)
    {
        $builder->useAutowiring(true);
        $builder->addDefinitions([
            Class3::class => create(),
        ]);
        $container = $builder->build();
        $container->get(Class3::class);
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_same_method_can_be_called_multiple_times(ContainerBuilder $builder)
    {
        $builder->useAutowiring(false);
        $builder->addDefinitions([
            Class1::class => create()
                ->method('increment')
                ->method('increment'),
        ]);
        $container = $builder->build();

        $class = $container->get(Class1::class);
        $this->assertEquals(2, $class->count);
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_create_lazy_object(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            Property::class => create()
                ->property('foo', 'bar')
                ->lazy(),
        ]);
        $container = $builder->build();

        $object = $container->get(Property::class);

        self::assertInstanceOf(Property::class, $object);
        self::assertInstanceOf(LazyLoadingInterface::class, $object);
        self::assertFalse($object->isProxyInitialized());
        self::assertEquals('bar', $object->foo);
        self::assertTrue($object->isProxyInitialized());
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_property_injection_in_private_properties(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            PrivatePropertyInjection::class => create()
                ->property('private', 'foo')
                ->property('protected', 'bar'),
        ]);
        $container = $builder->build();

        $object = $container->get(PrivatePropertyInjection::class);

        self::assertEquals('foo', $object->getPrivate());
        self::assertEquals('bar', $object->getProtected());
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_property_injection_in_private_properties_of_parent_class(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            PrivatePropertyInjection::class => create()
                ->property('private', 'parent')
                ->property('protected', 'bar'),
            PrivatePropertyInjectionSubClass::class => create()
                ->property('private', 'child')
                ->property('protected', 'overloaded'),
        ]);
        $container = $builder->build();

        $object = $container->get(PrivatePropertyInjectionSubClass::class);

        // For now it's not possible to define private properties in parent classes using array config
        self::assertEquals(null, $object->getPrivate());
        self::assertEquals('overloaded', $object->getProtected());
        self::assertEquals('child', $object->getSubClassPrivate());
    }

    /**
     * @dataProvider provideContainer
     * @expectedException \Exception
     * @expectedExceptionMessage Property stdClass::$foo does not exist
     */
    public function test_property_injection_in_unknown_property(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            \stdClass::class => create()
                ->property('foo', 'bar'),
        ]);
        $container = $builder->build();
        $container->get(\stdClass::class);
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_create_in_array(ContainerBuilder $builder)
    {
        $container = $builder->addDefinitions([
            'foo' => [
                'bar' => create(Property::class),
            ],
        ])->build();

        self::assertInstanceOf(Property::class, $container->get('foo')['bar']);
    }
}

namespace DI\Test\IntegrationTest\Definitions\CreateDefinitionTest;

class Property
{
    public $foo;
}

class ConstructorInjection
{
    public $value;
    public $scalarValue;
    public $typedValue;
    public $typedOptionalValue;
    /** @var \ProxyManager\Proxy\LazyLoadingInterface */
    public $lazyService;
    public $optionalValue;

    public function __construct(
        $value,
        string $scalarValue,
        \stdClass $typedValue,
        \stdClass $typedOptionalValue = null,
        \stdClass $lazyService,
        $optionalValue = 'hello'
    ) {
        $this->value = $value;
        $this->scalarValue = $scalarValue;
        $this->typedValue = $typedValue;
        $this->typedOptionalValue = $typedOptionalValue;
        $this->lazyService = $lazyService;
        $this->optionalValue = $optionalValue;
    }
}

class PropertyInjection
{
    public $value;
    public $entry;
    /** @var \ProxyManager\Proxy\LazyLoadingInterface */
    public $lazyService;
}

class MethodInjection
{
    public $value;
    public $scalarValue;
    public $typedValue;
    public $typedOptionalValue;
    /** @var \ProxyManager\Proxy\LazyLoadingInterface */
    public $lazyService;
    public $optionalValue;

    public function method(
        $value,
        string $scalarValue,
        \stdClass $typedValue,
        \stdClass $typedOptionalValue = null,
        \stdClass $lazyService,
        $optionalValue = 'hello'
    ) {
        $this->value = $value;
        $this->scalarValue = $scalarValue;
        $this->typedValue = $typedValue;
        $this->typedOptionalValue = $typedOptionalValue;
        $this->lazyService = $lazyService;
        $this->optionalValue = $optionalValue;
    }
}

class PrivatePropertyInjection
{
    private $private;
    protected $protected;

    public function getPrivate()
    {
        return $this->private;
    }

    public function getProtected()
    {
        return $this->protected;
    }
}

class PrivatePropertyInjectionSubClass extends PrivatePropertyInjection
{
    private $private;

    public function getSubClassPrivate()
    {
        return $this->private;
    }
}
