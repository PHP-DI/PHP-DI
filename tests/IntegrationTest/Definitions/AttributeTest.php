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
use function DI\create;

/**
 * Test definitions autowired with attributes.
 *
 * @requires PHP >= 8
 */
class AttributeTest extends BaseContainerTest
{
    public static function setUpBeforeClass(): void
    {
        if (PHP_VERSION_ID < 80400) {
            require_once __DIR__ . '/Attribute/class-php83.php';
        } else {
            require_once __DIR__ . '/Attribute/class.php';
        }
    }

    /**
     * @dataProvider provideContainer
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideContainer')]
    public function test_injectable_annotation_is_not_required(ContainerBuilder $builder)
    {
        $container = $builder->useAttributes(true)->build();
        self::assertInstanceOf(NonAnnotatedClass::class, $container->get(NonAnnotatedClass::class));
    }

    /**
     * @dataProvider provideContainer
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideContainer')]
    public function test_constructor_injection(ContainerBuilder $builder)
    {
        $builder->useAttributes(true);
        $builder->addDefinitions([
            'foo' => 'bar',
            'lazyService' => autowire(\stdClass::class)->lazy(),
            'attribute' => create(\stdClass::class),
        ]);
        $container = $builder->build();

        $object = $container->get(ConstructorInjection::class);

        self::assertEquals(new \stdClass, $object->typedValue);
        self::assertEquals(PHP_VERSION_ID < 80400 ? null : new \stdClass, $object->typedOptionalValue);
        self::assertNull($object->typedOptionalValueDefaultNull);
        self::assertEquals('bar', $object->value);
        self::assertInstanceOf(\stdClass::class, $object->lazyService);
        self::assertInstanceOf(LazyLoadingInterface::class, $object->lazyService);
        self::assertFalse($object->lazyService->isProxyInitialized());
        self::assertSame($container->get('attribute'), $object->attribute);
        self::assertEquals('hello', $object->optionalValue);
    }

    /**
     * @dataProvider provideContainer
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideContainer')]
    public function test_supports_autowiring(ContainerBuilder $builder)
    {
        $builder->useAttributes(true);
        $container = $builder->build();

        $object = $container->get(AutowiredClass::class);

        self::assertEquals(new \stdClass, $object->entry);
    }

    /**
     * @dataProvider provideContainer
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideContainer')]
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
    #[\PHPUnit\Framework\Attributes\DataProvider('provideContainer')]
    public function test_method_injection(ContainerBuilder $builder)
    {
        $builder->useAttributes(true);
        $builder->addDefinitions([
            'foo' => 'bar',
            'lazyService' => autowire(\stdClass::class)->lazy(),
            'attribute' => create(\stdClass::class),
        ]);
        $container = $builder->build();

        $object = $container->get(ConstructorInjection::class);

        self::assertEquals(new \stdClass, $object->typedValue);
        self::assertEquals(PHP_VERSION_ID < 80400 ? null : new \stdClass, $object->typedOptionalValue);
        self::assertNull($object->typedOptionalValueDefaultNull);
        self::assertEquals('bar', $object->value);
        self::assertInstanceOf(\stdClass::class, $object->lazyService);
        self::assertInstanceOf(LazyLoadingInterface::class, $object->lazyService);
        self::assertFalse($object->lazyService->isProxyInitialized());
        self::assertSame($container->get('attribute'), $object->attribute);
        self::assertEquals('hello', $object->optionalValue);
    }
}
