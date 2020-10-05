<?php

declare(strict_types=1);

namespace DI\Test\IntegrationTest\Definitions;

use DI\ContainerBuilder;
use DI\Test\IntegrationTest\BaseContainerTest;
use DI\Test\IntegrationTest\Definitions\AttributesTest\AutowiredClass;
use DI\Test\IntegrationTest\Definitions\AttributesTest\ConstructorInjection;
use DI\Test\IntegrationTest\Definitions\AttributesTest\NonAnnotatedClass;
use DI\Test\IntegrationTest\Definitions\AttributesTest\PropertyInjection;
use ProxyManager\Proxy\LazyLoadingInterface;
use function DI\autowire;

/**
 * Test definitions autowired with attributes.
 *
 * @requires PHP >= 8
 */
class AttributeTest extends BaseContainerTest
{
    /**
     * @dataProvider provideContainer
     */
    public function test_injectable_annotation_is_not_required(ContainerBuilder $builder)
    {
        $container = $builder->useAttributes(true)->build();
        self::assertInstanceOf(NonAnnotatedClass::class, $container->get(NonAnnotatedClass::class));
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_supports_autowiring(ContainerBuilder $builder)
    {
        $builder->useAttributes(true);
        $builder->addDefinitions([
            'foo' => 'bar',
            'lazyService' => autowire(\stdClass::class)->lazy(),
        ]);
        $container = $builder->build();

        $object = $container->get(ConstructorInjection::class);

        self::assertEquals(new \stdClass, $object->typedValue);
        self::assertEquals(new \stdClass, $object->typedOptionalValue);
        self::assertEquals('bar', $object->value);
        self::assertInstanceOf(\stdClass::class, $object->lazyService);
        self::assertInstanceOf(LazyLoadingInterface::class, $object->lazyService);
        self::assertFalse($object->lazyService->isProxyInitialized());
        self::assertEquals('hello', $object->optionalValue);
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_constructor_injection(ContainerBuilder $builder)
    {
        $builder->useAttributes(true);
        $container = $builder->build();

        $object = $container->get(AutowiredClass::class);

        self::assertEquals(new \stdClass, $object->entry);
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_property_injection(ContainerBuilder $builder)
    {
        $builder->useAttributes(true);
        $builder->addDefinitions([
            'foo' => 'bar',
            'lazyService' => autowire(\stdClass::class)->lazy(),
        ]);
        $container = $builder->build();

        $object = $container->get(PropertyInjection::class);

        self::assertEquals('bar', $object->value);
        self::assertEquals('bar', $object->value2);
        self::assertInstanceOf(\stdClass::class, $object->entry);
        self::assertInstanceOf(\stdClass::class, $object->lazyService);
        self::assertInstanceOf(LazyLoadingInterface::class, $object->lazyService);
        self::assertFalse($object->lazyService->isProxyInitialized());
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_method_injection(ContainerBuilder $builder)
    {
        $builder->useAttributes(true);
        $builder->addDefinitions([
            'foo' => 'bar',
            'lazyService' => autowire(\stdClass::class)->lazy(),
        ]);
        $container = $builder->build();

        $object = $container->get(ConstructorInjection::class);

        self::assertEquals(new \stdClass, $object->typedValue);
        self::assertEquals(new \stdClass, $object->typedOptionalValue);
        self::assertEquals('bar', $object->value);
        self::assertInstanceOf(\stdClass::class, $object->lazyService);
        self::assertInstanceOf(LazyLoadingInterface::class, $object->lazyService);
        self::assertFalse($object->lazyService->isProxyInitialized());
        self::assertEquals('hello', $object->optionalValue);
    }
}

namespace DI\Test\IntegrationTest\Definitions\AttributesTest;

use DI\Attribute\Inject;
use stdClass;

class NonAnnotatedClass
{
}

class NamespacedClass
{
}

class AutowiredClass
{
    public stdClass $entry;
    public function __construct(stdClass $entry)
    {
        $this->entry = $entry;
    }
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

    #[Inject(['value' => 'foo', 'scalarValue' => 'foo', 'lazyService' => 'lazyService'])]
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
    #[Inject(name: 'foo')]
    public $value;
    #[Inject('foo')]
    public $value2;
    #[Inject]
    public stdClass $entry;
    #[Inject('lazyService')]
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

    #[Inject(['value' => 'foo', 'scalarValue' => 'foo', 'lazyService' => 'lazyService'])]
    public function method(
        $value,
        string $scalarValue,
        $untypedValue,
        \stdClass $typedOptionalValue = null,
        \stdClass $lazyService,
        $optionalValue = 'hello'
    ) {
        $this->value = $value;
        $this->scalarValue = $scalarValue;
        $this->untypedValue = $untypedValue;
        $this->typedOptionalValue = $typedOptionalValue;
        $this->lazyService = $lazyService;
        $this->optionalValue = $optionalValue;
    }
}
