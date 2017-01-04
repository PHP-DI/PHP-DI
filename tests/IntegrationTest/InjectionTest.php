<?php

namespace DI\Test\IntegrationTest;

use DI\Container;
use DI\ContainerBuilder;
use DI\Scope;
use DI\Test\IntegrationTest\Fixtures\Class1;
use DI\Test\IntegrationTest\Fixtures\Class2;
use DI\Test\IntegrationTest\Fixtures\Implementation1;
use DI\Test\IntegrationTest\Fixtures\Interface1;
use DI\Test\IntegrationTest\Fixtures\LazyDependency;
use ProxyManager\Proxy\LazyLoadingInterface;

/**
 * Test class for injection.
 *
 * @coversNothing Because integration test
 */
class InjectionTest extends \PHPUnit_Framework_TestCase
{
    const DEFINITION_REFLECTION = 1;
    const DEFINITION_ANNOTATIONS = 2;
    const DEFINITION_ARRAY = 3;
    const DEFINITION_PHP = 4;

    /**
     * PHPUnit data provider: generates container configurations for running the same tests
     * for each configuration possible.
     * @return array
     */
    public static function containerProvider()
    {
        // Test with a container using reflection
        $builder = new ContainerBuilder();
        $builder->useAutowiring(true);
        $builder->useAnnotations(false);
        $containerReflection = $builder->build();
        // We have to define some entries for the test because reflection on itself doesn't make it possible
        $containerReflection->set('foo', 'bar');
        $containerReflection->set(
            Interface1::class,
            \DI\create(Implementation1::class)
        );
        $containerReflection->set('namedDependency', \DI\create(Class2::class));
        $containerReflection->set(LazyDependency::class, \DI\create()->lazy());
        $containerReflection->set('alias', \DI\get('namedDependency'));

        // Test with a container using annotations and reflection
        $builder = new ContainerBuilder();
        $builder->useAutowiring(true);
        $builder->useAnnotations(true);
        $containerAnnotations = $builder->build();
        // We have to define some entries for the test because annotations on itself doesn't make it possible
        $containerAnnotations->set('foo', 'bar');
        $containerAnnotations->set(Interface1::class, \DI\create(Implementation1::class));
        $containerAnnotations->set('namedDependency', \DI\create(Class2::class));
        $containerAnnotations->set('alias', \DI\get('namedDependency'));

        // Test with a container using array configuration
        $builder = new ContainerBuilder();
        $builder->useAutowiring(false);
        $builder->useAnnotations(false);
        $builder->addDefinitions(__DIR__ . '/Fixtures/definitions.php');
        $containerArray = $builder->build();

        // Test with a container using PHP configuration
        $builder = new ContainerBuilder();
        $builder->useAutowiring(false);
        $builder->useAnnotations(false);
        $containerPHP = $builder->build();
        $containerPHP->set('foo', 'bar');
        $containerPHP->set(
            Class1::class,
            \DI\create()
                ->scope(Scope::PROTOTYPE)
                ->property('property1', \DI\get(Class2::class))
                ->property('property2', \DI\get(Interface1::class))
                ->property('property3', \DI\get('namedDependency'))
                ->property('property4', \DI\get('foo'))
                ->property('property5', \DI\get(LazyDependency::class))
                ->constructor(
                    \DI\get(Class2::class),
                    \DI\get(Interface1::class),
                    \DI\get(LazyDependency::class)
                )
                ->method('method1', \DI\get(Class2::class))
                ->method('method2', \DI\get(Interface1::class))
                ->method('method3', \DI\get('namedDependency'), \DI\get('foo'))
                ->method('method4', \DI\get(LazyDependency::class))
                ->methodParameter('method5', 'param1', \DI\get(Interface1::class))
                ->methodParameter('method5', 'param2', \DI\get('foo'))
        );
        $containerPHP->set(Class2::class, \DI\create());
        $containerPHP->set(Implementation1::class, \DI\create());
        $containerPHP->set(
            Interface1::class,
            \DI\create(Implementation1::class)
                ->scope(Scope::SINGLETON)
        );
        $containerPHP->set('namedDependency', \DI\create(Class2::class));
        $containerPHP->set(LazyDependency::class, \DI\create()->lazy());
        $containerPHP->set('alias', \DI\get('namedDependency'));

        return [
            'autowiring' => [self::DEFINITION_REFLECTION, $containerReflection],
            'annotation' => [self::DEFINITION_ANNOTATIONS, $containerAnnotations],
            'array'      => [self::DEFINITION_ARRAY, $containerArray],
            'php'        => [self::DEFINITION_PHP, $containerPHP],
        ];
    }

    /**
     * @dataProvider containerProvider
     */
    public function testContainerHas($type, Container $container)
    {
        $this->assertTrue($container->has(Class1::class));
        $this->assertTrue($container->has(Class2::class));
        $this->assertTrue($container->has(Interface1::class));
        $this->assertTrue($container->has('namedDependency'));
        $this->assertTrue($container->has(LazyDependency::class));
        $this->assertFalse($container->has('unknown'));
    }

    /**
     * @dataProvider containerProvider
     */
    public function testGet($type, Container $container)
    {
        $obj = $container->get(Class1::class);

        $proxies = [];

        $proxies[] = $this->validateConstructorInjection($obj, $type);

        // Only constructor injection with reflection
        if ($type != self::DEFINITION_REFLECTION) {
            $proxies[] = $this->validatePropertyInjection($obj);
            $proxies[] = $this->validateMethodInjection($obj);
        }

        // The proxies are checked last, else there is no lazy injection once they are resolved
        $this->validateProxyResolution($proxies);
    }

    /**
     * @dataProvider containerProvider
     */
    public function testMake($type, Container $container)
    {
        $obj = $container->make(Class1::class);

        $proxies = [];

        $proxies[] = $this->validateConstructorInjection($obj, $type);

        // Only constructor injection with reflection
        if ($type != self::DEFINITION_REFLECTION) {
            $proxies[] = $this->validatePropertyInjection($obj);
            $proxies[] = $this->validateMethodInjection($obj);
        }

        // The proxies are checked last, else there is no lazy injection once they are resolved
        $this->validateProxyResolution($proxies);
    }

    /**
     * @dataProvider containerProvider
     */
    public function testInjectOn($type, Container $container)
    {
        $obj = new Class1(new Class2(), new Implementation1(), new LazyDependency());
        $container->injectOn($obj);

        $proxies = [];

        // Only constructor injection with autowiring
        if ($type != self::DEFINITION_REFLECTION) {
            $proxies[] = $this->validatePropertyInjection($obj);
            $proxies[] = $this->validateMethodInjection($obj);
        }

        // The proxies are checked last, else there is no lazy injection once they are resolved
        $this->validateProxyResolution($proxies);
    }

    /**
     * @dataProvider containerProvider
     */
    public function testScope($type, Container $container)
    {
        // No scope definition possible with autowiring only
        if ($type == self::DEFINITION_REFLECTION) {
            return;
        }
        $class1_1 = $container->get(Class1::class);
        $class1_2 = $container->get(Class1::class);
        $this->assertNotSame($class1_1, $class1_2);
        $class2_1 = $container->get(Class2::class);
        $class2_2 = $container->get(Class2::class);
        $this->assertSame($class2_1, $class2_2);
        $class3_1 = $container->get(Interface1::class);
        $class3_2 = $container->get(Interface1::class);
        $this->assertSame($class3_1, $class3_2);
    }

    /**
     * @dataProvider containerProvider
     */
    public function testAlias($type, Container $container)
    {
        $this->assertInstanceOf(Class2::class, $container->get('alias'));
    }

    private function validateConstructorInjection(Class1 $class1, $type)
    {
        $this->assertInstanceOf(Class2::class, $class1->constructorParam1);
        $this->assertInstanceOf(Implementation1::class, $class1->constructorParam2);

        // Test lazy injection (not possible using autowiring only)
        if ($type != self::DEFINITION_REFLECTION) {
            $this->assertInstanceOf(LazyDependency::class, $class1->constructorParam3);
            $this->assertInstanceOf(LazyLoadingInterface::class, $class1->constructorParam3);
            /** @var LazyDependency|LazyLoadingInterface $proxy */
            $proxy = $class1->constructorParam3;
            $this->assertFalse($proxy->isProxyInitialized());

            return $proxy;
        }

        return null;
    }

    private function validatePropertyInjection(Class1 $class1)
    {
        $this->assertInstanceOf(Class2::class, $class1->property1);
        $this->assertInstanceOf(Implementation1::class, $class1->property2);
        $this->assertInstanceOf(Class2::class, $class1->property3);
        $this->assertEquals('bar', $class1->property4);
        // Lazy injection
        /** @var LazyDependency|LazyLoadingInterface $proxy */
        $proxy = $class1->property5;
        $this->assertInstanceOf(LazyDependency::class, $proxy);
        $this->assertInstanceOf(LazyLoadingInterface::class, $proxy);
        $this->assertFalse($proxy->isProxyInitialized());

        return $proxy;
    }

    private function validateMethodInjection(Class1 $class1)
    {
        // Method 1 (automatic resolution with type hinting, optional parameter not overridden)
        $this->assertInstanceOf(Class2::class, $class1->method1Param1);

        // Method 2 (automatic resolution with type hinting)
        $this->assertInstanceOf(Implementation1::class, $class1->method2Param1);

        // Method 3 (defining parameters with the annotation)
        $this->assertInstanceOf(Class2::class, $class1->method3Param1);
        $this->assertEquals('bar', $class1->method3Param2);

        // Method 4 (lazy)
        $this->assertInstanceOf(LazyDependency::class, $class1->method4Param1);
        $this->assertInstanceOf(LazyLoadingInterface::class, $class1->method4Param1);
        // Lazy injection
        /** @var LazyDependency|LazyLoadingInterface $proxy */
        $proxy = $class1->method4Param1;
        $this->assertFalse($proxy->isProxyInitialized());

        // Method 5 (defining a parameter by its name)
        $this->assertInstanceOf(Implementation1::class, $class1->method5Param1);
        $this->assertEquals('bar', $class1->method5Param2);

        return $proxy;
    }

    /**
     * Validate that the proxy resolves correctly.
     * @param LazyDependency[]|LazyLoadingInterface[] $proxies
     */
    private function validateProxyResolution($proxies)
    {
        foreach ($proxies as $proxy) {
            if ($proxy) {
                $this->assertTrue($proxy->getValue());
                $this->assertTrue($proxy->isProxyInitialized());
            }
        }
    }
}
