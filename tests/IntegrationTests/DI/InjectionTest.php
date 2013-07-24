<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.io/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace IntegrationTests\DI;

use DI\Definition\FileLoader\ArrayDefinitionFileLoader;
use DI\Definition\FileLoader\YamlDefinitionFileLoader;
use DI\Scope;
use DI\Container;
use IntegrationTests\DI\Fixtures\Class1;
use IntegrationTests\DI\Fixtures\Class2;
use IntegrationTests\DI\Fixtures\Implementation1;
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
    const DEFINITION_YAML = 6;

    /**
     * PHPUnit data provider: generates container configurations for running the same tests for each configuration possible
     * @return array
     */
    public static function containerProvider()
    {
        // Test with a container using reflection
        $containerReflection = new Container();
        $containerReflection->useReflection(true);
        $containerReflection->useAnnotations(false);
        $containerReflection->addDefinitions(
            array(
                'foo'                                     => 'bar',
                'IntegrationTests\DI\Fixtures\Interface1' => array(
                    'class' => 'IntegrationTests\DI\Fixtures\Implementation1',
                ),
                'namedDependency'                         => array(
                    'class' => 'IntegrationTests\DI\Fixtures\Class2',
                ),
            )
        );

        // Test with a container using annotations and reflection
        $containerAnnotations = new Container();
        $containerAnnotations->useReflection(true);
        $containerAnnotations->useAnnotations(true);
        $containerAnnotations->addDefinitions(
            array(
                'foo'                                     => 'bar',
                'IntegrationTests\DI\Fixtures\Interface1' => array(
                    'class' => 'IntegrationTests\DI\Fixtures\Implementation1',
                ),
                'namedDependency'                         => array(
                    'class' => 'IntegrationTests\DI\Fixtures\Class2',
                ),
            )
        );

        // Test with a container using array configuration
        $containerArray = new Container();
        $containerArray->useReflection(false);
        $containerArray->useAnnotations(false);
        $array = require __DIR__ . '/Fixtures/definitions.php';
        $containerArray->addDefinitions($array);

        // Test with a container using PHP configuration
        $containerPHP = new Container();
        $containerPHP->useReflection(false);
        $containerPHP->useAnnotations(false);
        $containerPHP->set('foo', 'bar');
        $containerPHP->set('IntegrationTests\DI\Fixtures\Class1')
            ->withScope(Scope::PROTOTYPE())
            ->withProperty('property1', 'IntegrationTests\DI\Fixtures\Class2')
            ->withProperty('property2', 'IntegrationTests\DI\Fixtures\Interface1')
            ->withProperty('property3', 'namedDependency')
            ->withProperty('property4', 'foo')
            ->withProperty('property5', 'IntegrationTests\DI\Fixtures\LazyDependency', true)
            ->withConstructor(
                array(
                    'param1' => 'IntegrationTests\DI\Fixtures\Class2',
                    'param2' => 'IntegrationTests\DI\Fixtures\Interface1',
                )
            )
            ->withMethod('method1', array('IntegrationTests\DI\Fixtures\Class2'))
            ->withMethod('method2', array('IntegrationTests\DI\Fixtures\Interface1'))
            ->withMethod('method3', array('param1' => 'namedDependency', 'param2' => 'foo'))
            ->withMethod('method4', array('param1' => array(
                    'name' => 'IntegrationTests\DI\Fixtures\LazyDependency',
                    'lazy' => true,
                )));
        $containerPHP->set('IntegrationTests\DI\Fixtures\Class2');
        $containerPHP->set('IntegrationTests\DI\Fixtures\Implementation1');
        $containerPHP->set('IntegrationTests\DI\Fixtures\Interface1')
            ->bindTo('IntegrationTests\DI\Fixtures\Implementation1')
            ->withScope(Scope::SINGLETON());
        $containerPHP->set('namedDependency')
            ->bindTo('IntegrationTests\DI\Fixtures\Class2');
        $containerPHP->set('IntegrationTests\DI\Fixtures\LazyDependency');

        // Test with a container using array configuration loaded from file
        $containerArrayFromFile = new Container();
        $containerArrayFromFile->useReflection(false);
        $containerArrayFromFile->useAnnotations(false);
        $containerArrayFromFile->addDefinitionsFromFile(new ArrayDefinitionFileLoader(__DIR__ . '/Fixtures/definitions.php'));

        // Test with a container using array configuration loaded from file
        $containerYaml = new Container();
        $containerYaml->useReflection(false);
        $containerYaml->useAnnotations(false);
        $containerYaml->addDefinitionsFromFile(new YamlDefinitionFileLoader(__DIR__ . '/Fixtures/definitions.yml'));

        return array(
            array(self::DEFINITION_REFLECTION, $containerReflection),
            array(self::DEFINITION_ANNOTATIONS, $containerAnnotations),
            array(self::DEFINITION_ARRAY, $containerArray),
            array(self::DEFINITION_PHP, $containerPHP),
            array(self::DEFINITION_ARRAY_FROM_FILE, $containerArrayFromFile),
            array(self::DEFINITION_YAML, $containerYaml),
        );
    }

    /**
     * @dataProvider containerProvider
     */
    public function testConstructorInjection($type, Container $container)
    {
        /** @var $class1 Class1 */
        $class1 = $container->get('IntegrationTests\DI\Fixtures\Class1');
        $this->assertInstanceOf('IntegrationTests\DI\Fixtures\Class2', $class1->constructorParam1);
        $this->assertInstanceOf('IntegrationTests\DI\Fixtures\Implementation1', $class1->constructorParam2);
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
        $class1 = $container->get('IntegrationTests\DI\Fixtures\Class1');
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
        $class1 = new Class1(new Class2(), new Implementation1());
        $container->inject($class1);
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
        $class1 = $container->get('IntegrationTests\DI\Fixtures\Class1');
        $this->assertInstanceOf('IntegrationTests\DI\Fixtures\Class2', $class1->method1Param1);
        $this->assertInstanceOf('IntegrationTests\DI\Fixtures\Implementation1', $class1->method2Param1);
        $this->assertInstanceOf('IntegrationTests\DI\Fixtures\Class2', $class1->method3Param1);
        $this->assertEquals('bar', $class1->method3Param2);
        $this->assertInstanceOf('IntegrationTests\DI\Fixtures\LazyDependency', $class1->method4Param1);
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
        $class1 = new Class1(new Class2(), new Implementation1());
        $container->inject($class1);
        $this->assertInstanceOf('IntegrationTests\DI\Fixtures\Class2', $class1->method1Param1);
        $this->assertInstanceOf('IntegrationTests\DI\Fixtures\Implementation1', $class1->method2Param1);
        $this->assertInstanceOf('IntegrationTests\DI\Fixtures\Class2', $class1->method3Param1);
        $this->assertEquals('bar', $class1->method3Param2);
        $this->assertInstanceOf('IntegrationTests\DI\Fixtures\LazyDependency', $class1->method4Param1);
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

}
