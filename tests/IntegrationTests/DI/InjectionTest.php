<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.io/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace IntegrationTests\DI;

use DI\ContainerBuilder;
use DI\Entry;
use DI\Scope;
use DI\Container;
use IntegrationTests\DI\Fixtures\Class1;
use IntegrationTests\DI\Fixtures\Class2;
use IntegrationTests\DI\Fixtures\Implementation1;
use IntegrationTests\DI\Fixtures\Interface1;
use IntegrationTests\DI\Fixtures\LazyDependency;

/**
 * Test class for injection
 */
class InjectionTest extends \PHPUnit_Framework_TestCase
{
    const DEFINITION_REFLECTION = 1;
    const DEFINITION_ANNOTATIONS = 2;
    const DEFINITION_ARRAY = 3;
    const DEFINITION_PHP = 4;
    const DEFINITION_ARRAY_FROM_FILE = 5;

    /**
     * PHPUnit data provider: generates container configurations for running the same tests for each configuration possible
     * @return array
     */
    public static function containerProvider()
    {
        // Test with a container using reflection
        $builder = new ContainerBuilder();
        $builder->useReflection(true);
        $builder->useAnnotations(false);
        $containerReflection = $builder->build();
        $containerReflection->addDefinitions([
            'foo'             => 'bar',
            Interface1::class => Entry::object(Implementation1::class),
            'namedDependency' => Entry::object(Class2::class),
        ]);

        // Test with a container using annotations and reflection
        $builder = new ContainerBuilder();
        $builder->useReflection(true);
        $builder->useAnnotations(true);
        $containerAnnotations = $builder->build();
        $containerReflection->addDefinitions([
            'foo'             => 'bar',
            Interface1::class => Entry::object(Implementation1::class),
            'namedDependency' => Entry::object(Class2::class),
        ]);

        // Test with a container using array configuration
        $builder = new ContainerBuilder();
        $builder->useReflection(false);
        $builder->useAnnotations(false);
        $containerArray = $builder->build();
        $array = require __DIR__ . '/Fixtures/definitions.php';
        $containerArray->addDefinitions($array);

        // Test with a container using PHP configuration
        $builder = new ContainerBuilder();
        $builder->useReflection(false);
        $builder->useAnnotations(false);
        $containerPHP = $builder->build();
        $containerPHP->set('foo', 'bar');
        $containerPHP->set(
            Class1::class,
            Entry::object()
                ->withScope(Scope::PROTOTYPE())
                ->withProperty('property1', Entry::link(Class2::class))
                ->withProperty('property2', Entry::link(Interface1::class))
                ->withProperty('property3', Entry::link('namedDependency'))
                ->withProperty('property4', Entry::link('foo'))
                ->withProperty('property5', Entry::link(LazyDependency::class))
                ->withConstructor(Entry::link(Class2::class), Entry::link(Interface1::class), Entry::link(LazyDependency::class))
                ->withMethod('method1', Entry::link(Class2::class))
                ->withMethod('method2', Entry::link(Interface1::class))
                ->withMethod('method3', Entry::link('namedDependency'), Entry::link('foo'))
                ->withMethod('method4', Entry::link(LazyDependency::class))
        );
        $containerPHP->set(Class2::class, Entry::object());
        $containerPHP->set(Implementation1::class, Entry::object());
        $containerPHP->set(Interface1::class, Entry::object(Implementation1::class)
            ->withScope(Scope::SINGLETON()));
        $containerPHP->set('namedDependency', Entry::object(Class2::class));
        $containerPHP->set(LazyDependency::class, Entry::object());

        // Test with a container using array configuration loaded from file
        $builder = new ContainerBuilder();
        $builder->useReflection(false);
        $builder->useAnnotations(false);
        $containerArrayFromFile = $builder->build();
        $containerArrayFromFile->addDefinitions(require __DIR__ . '/Fixtures/definitions.php');

        return array(
            array(self::DEFINITION_REFLECTION, $containerReflection),
            array(self::DEFINITION_ANNOTATIONS, $containerAnnotations),
            array(self::DEFINITION_ARRAY, $containerArray),
            array(self::DEFINITION_PHP, $containerPHP),
            array(self::DEFINITION_ARRAY_FROM_FILE, $containerArrayFromFile),
        );
    }

    /**
     * @dataProvider containerProvider
     */
    public function testContainerHas($type, Container $container)
    {
        $this->assertTrue($container->has(Class1::class));
        $this->assertTrue($container->has('IntegrationTests\DI\Fixtures\Class2'));
        $this->assertTrue($container->has('IntegrationTests\DI\Fixtures\Interface1'));
        $this->assertTrue($container->has('namedDependency'));
        $this->assertTrue($container->has(LazyDependency::class));
    }

    /**
     * @dataProvider containerProvider
     */
    public function testConstructorInjection($type, Container $container)
    {
        /** @var $class1 Class1 */
        $class1 = $container->get(Class1::class);

        $this->assertInstanceOf('IntegrationTests\DI\Fixtures\Class2', $class1->constructorParam1);
        $this->assertInstanceOf('IntegrationTests\DI\Fixtures\Implementation1', $class1->constructorParam2);

        // Test lazy injection (not possible using reflection)
        if ($type != self::DEFINITION_REFLECTION) {
            $this->assertInstanceOf(LazyDependency::class, $class1->constructorParam3);
            $this->assertInstanceOf('ProxyManager\Proxy\LazyLoadingInterface', $class1->constructorParam3);
            /** @var LazyDependency|\ProxyManager\Proxy\LazyLoadingInterface $proxy */
            $proxy = $class1->constructorParam3;
            $this->assertFalse($proxy->isProxyInitialized());
            // Correct proxy resolution
            $this->assertTrue($proxy->getValue());
            $this->assertTrue($proxy->isProxyInitialized());
        }
    }

    /**
     * @dataProvider containerProvider
     */
    public function testPropertyInjection($type, Container $container)
    {
        // Only constructor injection with reflection
        if ($type == self::DEFINITION_REFLECTION) {
            return;
        }
        /** @var $class1 Class1 */
        $class1 = $container->get(Class1::class);

        $this->assertInstanceOf('IntegrationTests\DI\Fixtures\Class2', $class1->property1);
        $this->assertInstanceOf('IntegrationTests\DI\Fixtures\Implementation1', $class1->property2);
        $this->assertInstanceOf('IntegrationTests\DI\Fixtures\Class2', $class1->property3);
        $this->assertEquals('bar', $class1->property4);
        // Lazy injection
        /** @var LazyDependency|\ProxyManager\Proxy\LazyLoadingInterface $proxy */
        $proxy = $class1->property5;
        $this->assertInstanceOf(LazyDependency::class, $proxy);
        $this->assertInstanceOf('ProxyManager\Proxy\LazyLoadingInterface', $proxy);
        $this->assertFalse($proxy->isProxyInitialized());
        // Correct proxy resolution
        $this->assertTrue($proxy->getValue());
        $this->assertTrue($proxy->isProxyInitialized());
    }

    /**
     * @dataProvider containerProvider
     */
    public function testPropertyInjectionExistingObject($type, Container $container)
    {
        // Only constructor injection with reflection
        if ($type == self::DEFINITION_REFLECTION) {
            return;
        }
        /** @var $class1 Class1 */
        $class1 = new Class1(new Class2(), new Implementation1(), new LazyDependency());
        $container->injectOn($class1);

        $this->assertInstanceOf('IntegrationTests\DI\Fixtures\Class2', $class1->property1);
        $this->assertInstanceOf('IntegrationTests\DI\Fixtures\Implementation1', $class1->property2);
        $this->assertInstanceOf('IntegrationTests\DI\Fixtures\Class2', $class1->property3);
        $this->assertEquals('bar', $class1->property4);
        // Lazy injection
        /** @var LazyDependency|\ProxyManager\Proxy\LazyLoadingInterface $proxy */
        $proxy = $class1->property5;
        $this->assertInstanceOf(LazyDependency::class, $proxy);
        $this->assertInstanceOf('ProxyManager\Proxy\LazyLoadingInterface', $proxy);
        $this->assertFalse($proxy->isProxyInitialized());
        // Correct proxy resolution
        $this->assertTrue($proxy->getValue());
        $this->assertTrue($proxy->isProxyInitialized());
    }

    /**
     * @dataProvider containerProvider
     */
    public function testMethodInjection($type, Container $container)
    {
        // Only constructor injection with reflection
        if ($type == self::DEFINITION_REFLECTION) {
            return;
        }
        /** @var $class1 Class1 */
        $class1 = $container->get(Class1::class);

        $this->assertInstanceOf('IntegrationTests\DI\Fixtures\Class2', $class1->method1Param1);
        $this->assertInstanceOf('IntegrationTests\DI\Fixtures\Implementation1', $class1->method2Param1);
        $this->assertInstanceOf('IntegrationTests\DI\Fixtures\Class2', $class1->method3Param1);
        $this->assertEquals('bar', $class1->method3Param2);
        $this->assertInstanceOf(LazyDependency::class, $class1->method4Param1);
        $this->assertInstanceOf('ProxyManager\Proxy\LazyLoadingInterface', $class1->method4Param1);
        // Lazy injection
        /** @var LazyDependency|\ProxyManager\Proxy\LazyLoadingInterface $proxy */
        $proxy = $class1->method4Param1;
        $this->assertFalse($proxy->isProxyInitialized());
        // Correct proxy resolution
        $this->assertTrue($proxy->getValue());
        $this->assertTrue($proxy->isProxyInitialized());
    }

    /**
     * @dataProvider containerProvider
     */
    public function testMethodInjectionExistingObject($type, Container $container)
    {
        // Only constructor injection with reflection
        if ($type == self::DEFINITION_REFLECTION) {
            return;
        }
        /** @var $class1 Class1 */
        $class1 = new Class1(new Class2(), new Implementation1(), new LazyDependency());
        $container->injectOn($class1);

        $this->assertInstanceOf('IntegrationTests\DI\Fixtures\Class2', $class1->method1Param1);
        $this->assertInstanceOf('IntegrationTests\DI\Fixtures\Implementation1', $class1->method2Param1);
        $this->assertInstanceOf('IntegrationTests\DI\Fixtures\Class2', $class1->method3Param1);
        $this->assertEquals('bar', $class1->method3Param2);
        $this->assertInstanceOf(LazyDependency::class, $class1->method4Param1);
        $this->assertInstanceOf('ProxyManager\Proxy\LazyLoadingInterface', $class1->method4Param1);
        // Lazy injection
        /** @var LazyDependency|\ProxyManager\Proxy\LazyLoadingInterface $proxy */
        $proxy = $class1->method4Param1;
        $this->assertFalse($proxy->isProxyInitialized());
        // Correct proxy resolution
        $this->assertTrue($proxy->getValue());
        $this->assertTrue($proxy->isProxyInitialized());
    }

    /**
     * @dataProvider containerProvider
     */
    public function testScope($type, Container $container)
    {
        // Only constructor injection with reflection
        if ($type == self::DEFINITION_REFLECTION) {
            return;
        }
        $class1_1 = $container->get(Class1::class);
        $class1_2 = $container->get(Class1::class);
        $this->assertNotSame($class1_1, $class1_2);
        $class2_1 = $container->get('IntegrationTests\DI\Fixtures\Class2');
        $class2_2 = $container->get('IntegrationTests\DI\Fixtures\Class2');
        $this->assertSame($class2_1, $class2_2);
        $class3_1 = $container->get('IntegrationTests\DI\Fixtures\Interface1');
        $class3_2 = $container->get('IntegrationTests\DI\Fixtures\Interface1');
        $this->assertSame($class3_1, $class3_2);
    }

}
