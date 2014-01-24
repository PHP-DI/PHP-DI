<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace IntegrationTests\DI;

use DI\ContainerBuilder;
use DI\Scope;
use DI\Container;
use IntegrationTests\DI\Fixtures\Class1;
use IntegrationTests\DI\Fixtures\Class2;
use IntegrationTests\DI\Fixtures\Implementation1;
use IntegrationTests\DI\Fixtures\LazyDependency;

/**
 * Test class for injection
 *
 * @coversNothing Because integration test
 */
class InjectionTest extends \PHPUnit_Framework_TestCase
{
    const DEFINITION_REFLECTION = 0;
    const DEFINITION_ANNOTATIONS = 1;
    const DEFINITION_ARRAY = 2;
    const DEFINITION_PHP = 3;
    const DEFINITION_COMPILED_REFLECTION = 4;
    const DEFINITION_COMPILED_ANNOTATIONS = 5;
    const DEFINITION_COMPILED_ARRAY = 6;
    const DEFINITION_COMPILED_PHP = 7;

    public function setUp()
    {
        // Clear all files in directory
        foreach (glob(__DIR__ . '/compiled/*.php') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }

    /**
     * PHPUnit data provider: generates container configurations for running the same tests
     * for each possible configuration
     * @return array
     */
    public static function containerProvider()
    {
        // Test with a container using reflection
        $builder = new ContainerBuilder();
        $builder->useReflection(true);
        $builder->useAnnotations(false);
        $containerReflection = $builder->build();
        // We have to define some entries for the test because reflection on itself doesn't make it possible
        $containerReflection->set('foo', 'bar');
        $containerReflection->set(
            'IntegrationTests\DI\Fixtures\Interface1',
            \DI\object('IntegrationTests\DI\Fixtures\Implementation1')
        );
        $containerReflection->set('namedDependency', \DI\object('IntegrationTests\DI\Fixtures\Class2'));
        $containerReflection->set('IntegrationTests\DI\Fixtures\LazyDependency', \DI\object()->lazy());
        $containerReflection->set('alias', \DI\link('namedDependency'));
        $containerReflection->set('factory', \DI\factory(function () {
            return 42;
        }));

        // Test with a container using annotations and reflection
        $builder = new ContainerBuilder();
        $builder->useReflection(true);
        $builder->useAnnotations(true);
        $containerAnnotations = $builder->build();
        // We have to define some entries for the test because annotations on itself doesn't make it possible
        $containerAnnotations->set('foo', 'bar');
        $containerAnnotations->set(
            'IntegrationTests\DI\Fixtures\Interface1',
            \DI\object('IntegrationTests\DI\Fixtures\Implementation1')
        );
        $containerAnnotations->set('namedDependency', \DI\object('IntegrationTests\DI\Fixtures\Class2'));
        $containerAnnotations->set('alias', \DI\link('namedDependency'));
        $containerAnnotations->set('factory', \DI\factory(function () {
            return 42;
        }));

        // Test with a container using array configuration
        $builder = new ContainerBuilder();
        $builder->useReflection(false);
        $builder->useAnnotations(false);
        $builder->addDefinitions(__DIR__ . '/Fixtures/definitions.php');
        $containerArray = $builder->build();

        // Test with a container using PHP configuration
        $builder = new ContainerBuilder();
        $builder->useReflection(false);
        $builder->useAnnotations(false);
        $containerPHP = $builder->build();
        $containerPHP->set('foo', 'bar');
        $containerPHP->set(
            'IntegrationTests\DI\Fixtures\Class1',
            \DI\object()
                ->scope(Scope::PROTOTYPE())
                ->property('property1', \DI\link('IntegrationTests\DI\Fixtures\Class2'))
                ->property('property2', \DI\link('IntegrationTests\DI\Fixtures\Interface1'))
                ->property('property3', \DI\link('namedDependency'))
                ->property('property4', \DI\link('foo'))
                ->property('property5', \DI\link('IntegrationTests\DI\Fixtures\LazyDependency'))
                ->constructor(
                    \DI\link('IntegrationTests\DI\Fixtures\Class2'),
                    \DI\link('IntegrationTests\DI\Fixtures\Interface1'),
                    \DI\link('IntegrationTests\DI\Fixtures\LazyDependency')
                )
                ->method('method1', \DI\link('IntegrationTests\DI\Fixtures\Class2'))
                ->method('method2', \DI\link('IntegrationTests\DI\Fixtures\Interface1'))
                ->method('method3', \DI\link('namedDependency'), \DI\link('foo'))
                ->method('method4', \DI\link('IntegrationTests\DI\Fixtures\LazyDependency'))
                ->methodParameter('method5', 'param1', \DI\link('IntegrationTests\DI\Fixtures\Interface1'))
                ->methodParameter('method5', 'param2', \DI\link('foo'))
        );
        $containerPHP->set('IntegrationTests\DI\Fixtures\Class2', \DI\object());
        $containerPHP->set('IntegrationTests\DI\Fixtures\Implementation1', \DI\object());
        $containerPHP->set(
            'IntegrationTests\DI\Fixtures\Interface1',
            \DI\object('IntegrationTests\DI\Fixtures\Implementation1')
                ->scope(Scope::SINGLETON())
        );
        $containerPHP->set('namedDependency', \DI\object('IntegrationTests\DI\Fixtures\Class2'));
        $containerPHP->set('IntegrationTests\DI\Fixtures\LazyDependency', \DI\object()->lazy());
        $containerPHP->set('alias', \DI\link('namedDependency'));
        $containerPHP->set('factory', \DI\factory(function () {
            return 42;
        }));

        // Test with a compiled container using reflection
        $builder = new ContainerBuilder();
        $builder->compileContainer(__DIR__ . '/compiled');
        $builder->useReflection(true);
        $builder->useAnnotations(false);
        $compiledContainerReflection = $builder->build();
        // We have to define some entries for the test because reflection on itself doesn't make it possible
        $compiledContainerReflection->set('foo', 'bar');
        $compiledContainerReflection->set(
            'IntegrationTests\DI\Fixtures\Interface1',
            \DI\object('IntegrationTests\DI\Fixtures\Implementation1')
        );
        $compiledContainerReflection->set('namedDependency', \DI\object('IntegrationTests\DI\Fixtures\Class2'));
        $compiledContainerReflection->set('IntegrationTests\DI\Fixtures\LazyDependency', \DI\object()->lazy());
        $compiledContainerReflection->set('alias', \DI\link('namedDependency'));
        $compiledContainerReflection->set('factory', \DI\factory(function () {
            return 42;
        }));

        // Test with a compiled container using annotations and reflection
        $builder = new ContainerBuilder();
        $builder->useReflection(true);
        $builder->useAnnotations(true);
        $compiledContainerAnnotations = $builder->build();
        // We have to define some entries for the test because annotations on itself doesn't make it possible
        $compiledContainerAnnotations->set('foo', 'bar');
        $compiledContainerAnnotations->set(
            'IntegrationTests\DI\Fixtures\Interface1',
            \DI\object('IntegrationTests\DI\Fixtures\Implementation1')
        );
        $compiledContainerAnnotations->set('namedDependency', \DI\object('IntegrationTests\DI\Fixtures\Class2'));
        $compiledContainerAnnotations->set('alias', \DI\link('namedDependency'));
        $compiledContainerAnnotations->set('factory', \DI\factory(function () {
            return 42;
        }));

        // Test with a compiled container using array configuration
        $builder = new ContainerBuilder();
        $builder->useReflection(false);
        $builder->useAnnotations(false);
        $builder->addDefinitions(__DIR__ . '/Fixtures/definitions.php');
        $compiledContainerArray = $builder->build();

        // Test with a compiled container using PHP configuration
        $builder = new ContainerBuilder();
        $builder->useReflection(false);
        $builder->useAnnotations(false);
        $compiledContainerPHP = $builder->build();
        $compiledContainerPHP->set('foo', 'bar');
        $compiledContainerPHP->set(
            'IntegrationTests\DI\Fixtures\Class1',
            \DI\object()
                ->scope(Scope::PROTOTYPE())
                ->property('property1', \DI\link('IntegrationTests\DI\Fixtures\Class2'))
                ->property('property2', \DI\link('IntegrationTests\DI\Fixtures\Interface1'))
                ->property('property3', \DI\link('namedDependency'))
                ->property('property4', \DI\link('foo'))
                ->property('property5', \DI\link('IntegrationTests\DI\Fixtures\LazyDependency'))
                ->constructor(
                    \DI\link('IntegrationTests\DI\Fixtures\Class2'),
                    \DI\link('IntegrationTests\DI\Fixtures\Interface1'),
                    \DI\link('IntegrationTests\DI\Fixtures\LazyDependency')
                )
                ->method('method1', \DI\link('IntegrationTests\DI\Fixtures\Class2'))
                ->method('method2', \DI\link('IntegrationTests\DI\Fixtures\Interface1'))
                ->method('method3', \DI\link('namedDependency'), \DI\link('foo'))
                ->method('method4', \DI\link('IntegrationTests\DI\Fixtures\LazyDependency'))
                ->methodParameter('method5', 'param1', \DI\link('IntegrationTests\DI\Fixtures\Interface1'))
                ->methodParameter('method5', 'param2', \DI\link('foo'))
        );
        $compiledContainerPHP->set('IntegrationTests\DI\Fixtures\Class2', \DI\object());
        $compiledContainerPHP->set('IntegrationTests\DI\Fixtures\Implementation1', \DI\object());
        $compiledContainerPHP->set(
            'IntegrationTests\DI\Fixtures\Interface1',
            \DI\object('IntegrationTests\DI\Fixtures\Implementation1')
                ->scope(Scope::SINGLETON())
        );
        $compiledContainerPHP->set('namedDependency', \DI\object('IntegrationTests\DI\Fixtures\Class2'));
        $compiledContainerPHP->set('IntegrationTests\DI\Fixtures\LazyDependency', \DI\object()->lazy());
        $compiledContainerPHP->set('alias', \DI\link('namedDependency'));
        $compiledContainerPHP->set('factory', \DI\factory(function () {
            return 42;
        }));

        return array(
            'autowiring'     => array(self::DEFINITION_REFLECTION, $containerReflection),
            'annotation'     => array(self::DEFINITION_ANNOTATIONS, $containerAnnotations),
            'array'          => array(self::DEFINITION_ARRAY, $containerArray),
            'php'            => array(self::DEFINITION_PHP, $containerPHP),
            'refl-compiled'  => array(self::DEFINITION_COMPILED_REFLECTION, $compiledContainerReflection),
            'annot-compiled' => array(self::DEFINITION_COMPILED_ANNOTATIONS, $compiledContainerAnnotations),
            'array-compiled' => array(self::DEFINITION_COMPILED_ARRAY, $compiledContainerArray),
            'php-compiled'   => array(self::DEFINITION_COMPILED_PHP, $compiledContainerPHP),
        );
    }

    /**
     * @dataProvider containerProvider
     */
    public function testContainerHas($type, Container $container)
    {
        $this->assertTrue($container->has('IntegrationTests\DI\Fixtures\Class1'));
        $this->assertTrue($container->has('IntegrationTests\DI\Fixtures\Class2'));
        $this->assertTrue($container->has('IntegrationTests\DI\Fixtures\Interface1'));
        $this->assertTrue($container->has('namedDependency'));
        $this->assertTrue($container->has('IntegrationTests\DI\Fixtures\LazyDependency'));
        $this->assertFalse($container->has('unknown'));
    }

    /**
     * @dataProvider containerProvider
     */
    public function testGet($type, Container $container)
    {
        $obj = $container->get('IntegrationTests\DI\Fixtures\Class1');

        $proxies = array();

        $proxies[] = $this->validateConstructorInjection($obj, $type);

        // Only constructor injection with reflection
        if ($type != self::DEFINITION_REFLECTION && $type != self::DEFINITION_COMPILED_REFLECTION) {
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
        $obj = $container->make('IntegrationTests\DI\Fixtures\Class1');

        $proxies = array();

        $proxies[] = $this->validateConstructorInjection($obj, $type);

        // Only constructor injection with reflection
        if ($type != self::DEFINITION_REFLECTION && $type != self::DEFINITION_COMPILED_REFLECTION) {
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

        $proxies = array();

        // Only constructor injection with autowiring
        if ($type != self::DEFINITION_REFLECTION && $type != self::DEFINITION_COMPILED_REFLECTION) {
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
        if ($type == self::DEFINITION_REFLECTION || $type == self::DEFINITION_COMPILED_REFLECTION) {
            return;
        }
        $class1_1 = $container->get('IntegrationTests\DI\Fixtures\Class1');
        $class1_2 = $container->get('IntegrationTests\DI\Fixtures\Class1');
        $this->assertNotSame($class1_1, $class1_2);
        $class2_1 = $container->get('IntegrationTests\DI\Fixtures\Class2');
        $class2_2 = $container->get('IntegrationTests\DI\Fixtures\Class2');
        $this->assertSame($class2_1, $class2_2);
        $class3_1 = $container->get('IntegrationTests\DI\Fixtures\Interface1');
        $class3_2 = $container->get('IntegrationTests\DI\Fixtures\Interface1');
        $this->assertSame($class3_1, $class3_2);
    }

    /**
     * @dataProvider containerProvider
     */
    public function testAlias($type, Container $container)
    {
        $this->assertInstanceOf('IntegrationTests\DI\Fixtures\Class2', $container->get('alias'));
    }

    /**
     * @dataProvider containerProvider
     */
    public function testFactory($type, Container $container)
    {
        $this->assertEquals(42, $container->get('factory'));
    }

    private function validateConstructorInjection(Class1 $class1, $type)
    {
        $this->assertInstanceOf('IntegrationTests\DI\Fixtures\Class2', $class1->constructorParam1);
        $this->assertInstanceOf('IntegrationTests\DI\Fixtures\Implementation1', $class1->constructorParam2);

        // Test lazy injection (not possible using autowiring only)
        if ($type != self::DEFINITION_REFLECTION && $type != self::DEFINITION_COMPILED_REFLECTION) {
            $this->assertInstanceOf('IntegrationTests\DI\Fixtures\LazyDependency', $class1->constructorParam3);
            $this->assertInstanceOf('ProxyManager\Proxy\LazyLoadingInterface', $class1->constructorParam3);
            /** @var LazyDependency|\ProxyManager\Proxy\LazyLoadingInterface $proxy */
            $proxy = $class1->constructorParam3;
            $this->assertFalse($proxy->isProxyInitialized());
            return $proxy;
        }
        return null;
    }

    private function validatePropertyInjection(Class1 $class1)
    {
        $this->assertInstanceOf('IntegrationTests\DI\Fixtures\Class2', $class1->property1);
        $this->assertInstanceOf('IntegrationTests\DI\Fixtures\Implementation1', $class1->property2);
        $this->assertInstanceOf('IntegrationTests\DI\Fixtures\Class2', $class1->property3);
        $this->assertEquals('bar', $class1->property4);
        // Lazy injection
        /** @var LazyDependency|\ProxyManager\Proxy\LazyLoadingInterface $proxy */
        $proxy = $class1->property5;
        $this->assertInstanceOf('IntegrationTests\DI\Fixtures\LazyDependency', $proxy);
        $this->assertInstanceOf('ProxyManager\Proxy\LazyLoadingInterface', $proxy);
        $this->assertFalse($proxy->isProxyInitialized());
        return $proxy;
    }

    private function validateMethodInjection(Class1 $class1)
    {
        // Method 1 (automatic resolution with type hinting, optional parameter not overridden)
        $this->assertInstanceOf('IntegrationTests\DI\Fixtures\Class2', $class1->method1Param1);

        // Method 2 (automatic resolution with type hinting)
        $this->assertInstanceOf('IntegrationTests\DI\Fixtures\Implementation1', $class1->method2Param1);

        // Method 3 (defining parameters with the annotation)
        $this->assertInstanceOf('IntegrationTests\DI\Fixtures\Class2', $class1->method3Param1);
        $this->assertEquals('bar', $class1->method3Param2);

        // Method 4 (lazy)
        $this->assertInstanceOf('IntegrationTests\DI\Fixtures\LazyDependency', $class1->method4Param1);
        $this->assertInstanceOf('ProxyManager\Proxy\LazyLoadingInterface', $class1->method4Param1);
        // Lazy injection
        /** @var LazyDependency|\ProxyManager\Proxy\LazyLoadingInterface $proxy */
        $proxy = $class1->method4Param1;
        $this->assertFalse($proxy->isProxyInitialized());

        // Method 5 (defining a parameter by its name)
        $this->assertInstanceOf('IntegrationTests\DI\Fixtures\Implementation1', $class1->method5Param1);
        $this->assertEquals('bar', $class1->method5Param2);

        return $proxy;
    }

    /**
     * Validate that the proxy resolves correctly.
     * @param LazyDependency[]|\ProxyManager\Proxy\LazyLoadingInterface[] $proxies
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
