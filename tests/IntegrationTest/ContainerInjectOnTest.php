<?php

namespace DI\Test\IntegrationTest;

use DI\ContainerBuilder;
use DI\Test\IntegrationTest\Fixtures\Class1;
use DI\Test\IntegrationTest\Fixtures\Class2;
use DI\Test\IntegrationTest\Fixtures\Implementation1;
use DI\Test\IntegrationTest\Fixtures\Interface1;
use DI\Test\IntegrationTest\Fixtures\LazyDependency;
use ProxyManager\Proxy\LazyLoadingInterface;
use stdClass;
use function DI\create;
use function DI\get;

/**
 * Tests the injectOn() method from the container.
 */
class ContainerInjectOnTest extends BaseContainerTest
{
    /**
     * @dataProvider provideContainer
     */
    public function test_returns_the_same_object(ContainerBuilder $builder)
    {
        $instance = new stdClass();

        self::assertSame($instance, $builder->build()->injectOn($instance));
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_inject_on_null_returns_null(ContainerBuilder $builder)
    {
        self::assertNull($builder->build()->injectOn(null));
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_inject_on_object_using_annotations(ContainerBuilder $builder)
    {
        $builder->useAutowiring(true);
        $builder->useAnnotations(true);
        $builder->addDefinitions([
            'foo' => 'bar',
            Interface1::class => create(Implementation1::class),
            'namedDependency' => create(Class2::class),
        ]);
        $container = $builder->build();

        $obj = new Class1(new Class2, new Implementation1, new LazyDependency);

        $container->injectOn($obj);

        // Test property injections
        self::assertInstanceOf(Class2::class, $obj->property1);
        self::assertInstanceOf(Implementation1::class, $obj->property2);
        self::assertInstanceOf(Class2::class, $obj->property3);
        self::assertEquals('bar', $obj->property4);
        // Lazy injection
        /** @var LazyDependency|LazyLoadingInterface $proxy */
        $proxy = $obj->property5;
        self::assertInstanceOf(LazyDependency::class, $proxy);
        self::assertInstanceOf(LazyLoadingInterface::class, $proxy);
        self::assertFalse($proxy->isProxyInitialized());

        // Test method injections

        // Method 1 (automatic resolution with type hinting, optional parameter not overridden)
        self::assertInstanceOf(Class2::class, $obj->method1Param1);
        // Method 2 (automatic resolution with type hinting)
        self::assertInstanceOf(Implementation1::class, $obj->method2Param1);
        // Method 3 (defining parameters with the annotation)
        self::assertInstanceOf(Class2::class, $obj->method3Param1);
        self::assertEquals('bar', $obj->method3Param2);
        // Method 4 (lazy)
        self::assertInstanceOf(LazyDependency::class, $obj->method4Param1);
        self::assertInstanceOf(LazyLoadingInterface::class, $obj->method4Param1);
        // Lazy injection
        /** @var LazyDependency|LazyLoadingInterface $proxy */
        $proxy = $obj->method4Param1;
        self::assertFalse($proxy->isProxyInitialized());
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_inject_on_object_using_config(ContainerBuilder $builder)
    {
        $builder->useAutowiring(false);
        $builder->useAnnotations(false);
        $builder->addDefinitions([
            'foo' => 'bar',

            Class1::class => create()
                ->property('property1', get(Class2::class))
                ->property('property2', get(Interface1::class))
                ->property('property3', get('namedDependency'))
                ->property('property4', get('foo'))
                ->property('property5', get(LazyDependency::class))
                ->constructor(get(Class2::class), get(Interface1::class), get(LazyDependency::class))
                ->method('method1', get(Class2::class))
                ->method('method2', get(Interface1::class))
                ->method('method3', get('namedDependency'), get('foo'))
                ->method('method4', get(LazyDependency::class)),

            Class2::class => create(),

            Implementation1::class => create(),

            Interface1::class => create(Implementation1::class),

            'namedDependency' => create(Class2::class),

            LazyDependency::class => create()->lazy(),

            'alias' => get('namedDependency'),
        ]);
        $container = $builder->build();

        $obj = new Class1(new Class2, new Implementation1, new LazyDependency);

        $container->injectOn($obj);

        // Test property injections
        self::assertInstanceOf(Class2::class, $obj->property1);
        self::assertInstanceOf(Implementation1::class, $obj->property2);
        self::assertInstanceOf(Class2::class, $obj->property3);
        self::assertEquals('bar', $obj->property4);
        // Lazy injection
        /** @var LazyDependency|LazyLoadingInterface $proxy */
        $proxy = $obj->property5;
        self::assertInstanceOf(LazyDependency::class, $proxy);
        self::assertInstanceOf(LazyLoadingInterface::class, $proxy);
        self::assertFalse($proxy->isProxyInitialized());

        // Test method injections

        // Method 1 (automatic resolution with type hinting, optional parameter not overridden)
        self::assertInstanceOf(Class2::class, $obj->method1Param1);
        // Method 2 (automatic resolution with type hinting)
        self::assertInstanceOf(Implementation1::class, $obj->method2Param1);
        // Method 3 (defining parameters with the annotation)
        self::assertInstanceOf(Class2::class, $obj->method3Param1);
        self::assertEquals('bar', $obj->method3Param2);
        // Method 4 (lazy)
        self::assertInstanceOf(LazyDependency::class, $obj->method4Param1);
        self::assertInstanceOf(LazyLoadingInterface::class, $obj->method4Param1);
        // Lazy injection
        /** @var LazyDependency|LazyLoadingInterface $proxy */
        $proxy = $obj->method4Param1;
        self::assertFalse($proxy->isProxyInitialized());
    }
}
