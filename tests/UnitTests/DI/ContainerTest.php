<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.com/PHP-DI/
 * @copyright 2012 Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace UnitTests\DI;

use stdClass;
use ReflectionClass;
use ReflectionMethod;
use \DI\Container;

/**
 * Test class for Container
 */
class ContainerTest extends \PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        // Reset the singleton instance to ensure all tests are independent
        Container::reset();
    }


    public function testGetInstance()
    {
        $instance = Container::getInstance();
        $this->assertInstanceOf('\DI\Container', $instance);
        $instance2 = Container::getInstance();
        $this->assertSame($instance, $instance2);
    }

    public function testResetInstance()
    {
        $instance = Container::getInstance();
        $this->assertInstanceOf('\DI\Container', $instance);
        Container::reset();
        $instance2 = Container::getInstance();
        $this->assertNotSame($instance, $instance2);
    }

    public function testSetGet()
    {
        $container = Container::getInstance();
        $dummy = new stdClass();
        $container->set('key', $dummy);
        $this->assertSame($dummy, $container->get('key'));
    }

    /**
     * @expectedException \DI\NotFoundException
     */
    public function testGetNotFound()
    {
        $container = Container::getInstance();
        $container->get('key');
    }

    public function testGetWithClosure()
    {
        $container = Container::getInstance();
        $container->set(
            'key',
            function (Container $c) {
                return 'hello';
            }
        );
        $this->assertEquals('hello', $container->get('key'));
    }

    public function testGetWithClosureIsCached()
    {
        $container = Container::getInstance();
        $container->set(
            'key',
            function (Container $c) {
                return new stdClass();
            }
        );
        $instance1 = $container->get('key');
        $instance2 = $container->get('key');
        $this->assertSame($instance1, $instance2);
    }

    public function testGetWithFactory()
    {
        $container = Container::getInstance();
        $this->assertInstanceOf('stdClass', $container->get('stdClass'));
    }

    public function testGetWithFactoryIsPrototype()
    {
        $container = Container::getInstance();
        // With @Scope("prototype") annotation
        $instance1 = $container->get('UnitTests\DI\Fixtures\Prototype');
        $instance2 = $container->get('UnitTests\DI\Fixtures\Prototype');
        $this->assertNotSame($instance1, $instance2);
    }

    public function testGetWithFactoryIsSingleton()
    {
        $container = Container::getInstance();
        // Without @Scope annotation => default is Singleton
        $instance1 = $container->get('stdClass');
        $instance2 = $container->get('stdClass');
        $this->assertSame($instance1, $instance2);
        // With @Scope annotation
        $instance3 = $container->get('UnitTests\DI\Fixtures\Singleton');
        $instance4 = $container->get('UnitTests\DI\Fixtures\Singleton');
        $this->assertSame($instance3, $instance4);
    }

    /**
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage Value 'foobar' is not part of the enum DI\Scope
     */
    public function testGetWithInvalidScope()
    {
        $container = Container::getInstance();
        $container->get('UnitTests\DI\Fixtures\InvalidScope');
    }

    public function testGetWithProxy()
    {
        $container = Container::getInstance();
        $this->assertInstanceOf('\DI\Proxy\Proxy', $container->get('stdClass', true));
    }

    /**
     * Tests if instantiation unlock works. We should be able to create two instances of the same class.
     */
    public function testCircularDependencies()
    {
        $container = Container::getInstance();
        $instance1 = $container->get('UnitTests\DI\Fixtures\Prototype');
        $instance2 = $container->get('UnitTests\DI\Fixtures\Prototype');
    }

    /**
     * @expectedException \DI\DependencyException
     * @expectedExceptionMessage Circular dependency detected while trying to instantiate class '\UnitTests\DI\Fixtures\Class1CircularDependencies'.
     */
    public function testCircularDependenciesException()
    {
        $container = Container::getInstance();
        $container->get('\UnitTests\DI\Fixtures\Class1CircularDependencies');
    }

    public function testMetadataReader()
    {
        $container = Container::getInstance();
        /** @var $reader \DI\MetadataReader\MetadataReader */
        $reader = $this->getMockForAbstractClass('DI\\MetadataReader\\MetadataReader');
        $container->setMetadataReader($reader);
        $this->assertSame($reader, $container->getMetadataReader());
    }

    public function testAddConfigurationEmpty()
    {
        // Empty configuration, no errors
        Container::addConfiguration(array());
    }

    public function testAddConfigurationEntries1()
    {
        Container::addConfiguration(
            array(
                'entries' => array(),
            )
        );
    }

    /**
     * @depends testSetGet
     */
    public function testAddConfigurationEntries2()
    {
        Container::addConfiguration(
            array(
                'entries' => array(
                    'test' => 'success',
                ),
            )
        );
        $container = Container::getInstance();
        $this->assertEquals('success', $container->get('test'));
    }

    /**
     * @depends testSetGet
     */
    public function testArrayAccessGet()
    {
        $container = Container::getInstance();
        $dummy = new stdClass();
        $container->set('key', $dummy);
        $this->assertSame($dummy, $container['key']);
    }

    /**
     * @depends testArrayAccessGet
     */
    public function testArrayAccessSet()
    {
        $container = Container::getInstance();
        $dummy = new stdClass();
        $container['key'] = $dummy;
        $this->assertSame($dummy, $container['key']);
    }

    /**
     * @depends testArrayAccessGet
     */
    public function testArrayAccessExists()
    {
        $container = Container::getInstance();
        $dummy = new stdClass();
        $this->assertFalse(isset($container['key']));
        $container['key'] = $dummy;
        $this->assertTrue(isset($container['key']));
    }

    /**
     * @depends testArrayAccessGet
     */
    public function testArrayAccessExistsWithClassName()
    {
        $container = Container::getInstance();
        $this->assertTrue(isset($container['stdClass']));
    }

    /**
     * @depends testArrayAccessGet
     */
    public function testArrayAccessUnset()
    {
        $container = Container::getInstance();
        $dummy = new stdClass();
        $container['key'] = $dummy;
        unset($container['key']);
        $this->assertFalse(isset($container['key']));
    }

    public function testNewInstanceWithoutConstructor()
    {
        $container = Container::getInstance();
        $method = new ReflectionMethod(get_class($container), 'newInstanceWithoutConstructor');
        $method->setAccessible(true);
        $reflectionClass = new ReflectionClass('UnitTests\DI\Fixtures\NewInstanceWithoutConstructor');
        $this->assertInstanceOf(
            'UnitTests\DI\Fixtures\NewInstanceWithoutConstructor',
            $method->invoke($container, $reflectionClass)
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The name parameter must be of type string
     */
    public function testGetNonStringParameter()
    {
        $container = Container::getInstance();
        $container->get(new stdClass());
    }

    /**
     * @expectedException \DI\DependencyException
     * @expectedExceptionMessage null given, object instance expected
     */
    public function testInjectAllNull()
    {
        $container = Container::getInstance();
        $container->injectAll(null);
    }

    /**
     * @expectedException \DI\DependencyException
     * @expectedExceptionMessage object instance expected
     */
    public function testInjectAllNotObject()
    {
        $container = Container::getInstance();
        $container->injectAll("foo");
    }

}
