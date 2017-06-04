<?php

declare(strict_types=1);

namespace DI\Test\IntegrationTest\Definitions;

use DI\ContainerBuilder;
use DI\Test\IntegrationTest\BaseContainerTest;
use DI\Test\IntegrationTest\Definitions\AnnotationTest\ConstructorInjection;
use DI\Test\IntegrationTest\Definitions\AnnotationTest\NamespacedClass;
use DI\Test\IntegrationTest\Definitions\AnnotationTest\NonAnnotatedClass;
use DI\Test\IntegrationTest\Definitions\AnnotationTest\PropertyInjection;
use ProxyManager\Proxy\LazyLoadingInterface;
use function DI\autowire;

/**
 * Test definitions autowired with annotations.
 */
class AnnotationTest extends BaseContainerTest
{
    /**
     * @dataProvider provideContainer
     */
    public function test_injectable_annotation_is_not_required(ContainerBuilder $builder)
    {
        $container = $builder->useAnnotations(true)->build();
        self::assertInstanceOf(NonAnnotatedClass::class, $container->get(NonAnnotatedClass::class));
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_constructor_injection(ContainerBuilder $builder)
    {
        $builder->useAnnotations(true);
        $builder->addDefinitions([
            'foo' => 'bar',
            'lazyService' => autowire(\stdClass::class)->lazy(),
        ]);
        $container = $builder->build();

        $object = $container->get(ConstructorInjection::class);

        self::assertEquals(new \stdClass, $object->typedValue);
        self::assertEquals(new \stdClass, $object->untypedValue);
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
    public function test_property_injection(ContainerBuilder $builder)
    {
        $builder->useAnnotations(true);
        $builder->addDefinitions([
            'foo' => 'bar',
            'lazyService' => autowire(\stdClass::class)->lazy(),
        ]);
        $container = $builder->build();

        $object = $container->get(PropertyInjection::class);

        self::assertEquals('bar', $object->value);
        self::assertEquals('bar', $object->value2);
        self::assertInstanceOf(\stdClass::class, $object->entry);
        self::assertInstanceOf(NamespacedClass::class, $object->importedNamespace);
        self::assertInstanceOf(\stdClass::class, $object->lazyService);
        self::assertInstanceOf(LazyLoadingInterface::class, $object->lazyService);
        self::assertFalse($object->lazyService->isProxyInitialized());
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_method_injection(ContainerBuilder $builder)
    {
        $builder->useAnnotations(true);
        $builder->addDefinitions([
            'foo' => 'bar',
            'lazyService' => autowire(\stdClass::class)->lazy(),
        ]);
        $container = $builder->build();

        $object = $container->get(ConstructorInjection::class);

        self::assertEquals(new \stdClass, $object->typedValue);
        self::assertEquals(new \stdClass, $object->untypedValue);
        self::assertEquals(new \stdClass, $object->typedOptionalValue);
        self::assertEquals('bar', $object->value);
        self::assertInstanceOf(\stdClass::class, $object->lazyService);
        self::assertInstanceOf(LazyLoadingInterface::class, $object->lazyService);
        self::assertFalse($object->lazyService->isProxyInitialized());
        self::assertEquals('hello', $object->optionalValue);
    }
}

namespace DI\Test\IntegrationTest\Definitions\AnnotationTest;

use DI\Annotation\Inject;

class NonAnnotatedClass
{
}

class NamespacedClass
{
}

class ConstructorInjection
{
    public $value;
    public $scalarValue;
    public $typedValue;
    public $untypedValue;
    public $typedOptionalValue;
    /** @var \ProxyManager\Proxy\LazyLoadingInterface */
    public $lazyService;
    public $optionalValue;

    /**
     * @Inject({"value" = "foo", "scalarValue" = "foo", "lazyService" = "lazyService"})
     * @param \stdClass $untypedValue
     */
    public function __construct(
        $value,
        string $scalarValue,
        \stdClass $typedValue,
        $untypedValue,
        \stdClass $typedOptionalValue = null,
        \stdClass $lazyService,
        $optionalValue = 'hello'
    ) {
        $this->value = $value;
        $this->scalarValue = $scalarValue;
        $this->typedValue = $typedValue;
        $this->untypedValue = $untypedValue;
        $this->typedOptionalValue = $typedOptionalValue;
        $this->lazyService = $lazyService;
        $this->optionalValue = $optionalValue;
    }
}

class PropertyInjection
{
    /**
     * @Inject(name="foo")
     */
    public $value;
    /**
     * @Inject("foo")
     */
    public $value2;
    /**
     * @Inject
     * @var \stdClass
     */
    public $entry;
    /**
     * @Inject
     * @var NamespacedClass
     */
    public $importedNamespace;
    /**
     * @Inject("lazyService")
     */
    public $lazyService;
}

class MethodInjection
{
    public $value;
    public $scalarValue;
    public $typedValue;
    public $untypedValue;
    public $typedOptionalValue;
    /** @var \ProxyManager\Proxy\LazyLoadingInterface */
    public $lazyService;
    public $optionalValue;

    /**
     * @Inject({"value" = "foo", "scalarValue" = "foo", "lazyService" = "lazyService"})
     * @param \stdClass $untypedValue
     */
    public function method(
        $value,
        string $scalarValue,
        \stdClass $typedValue,
        $untypedValue,
        \stdClass $typedOptionalValue = null,
        \stdClass $lazyService,
        $optionalValue = 'hello'
    ) {
        $this->value = $value;
        $this->scalarValue = $scalarValue;
        $this->typedValue = $typedValue;
        $this->untypedValue = $untypedValue;
        $this->typedOptionalValue = $typedOptionalValue;
        $this->lazyService = $lazyService;
        $this->optionalValue = $optionalValue;
    }
}
