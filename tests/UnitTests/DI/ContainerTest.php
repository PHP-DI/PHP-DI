<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.io/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace UnitTests\DI;

use DI\ContainerBuilder;
use DI\Entry;
use stdClass;
use DI\Container;
use UnitTests\DI\Fixtures\Class1CircularDependencies;

/**
 * Test class for Container
 */
class ContainerTest extends \PHPUnit_Framework_TestCase
{
    public function testSetGet()
    {
        $container = ContainerBuilder::buildDevContainer();
        $dummy = new stdClass();
        $container->set('key', $dummy);
        $this->assertSame($dummy, $container->get('key'));
    }

    /**
     * @expectedException \DI\NotFoundException
     */
    public function testGetNotFound()
    {
        $container = ContainerBuilder::buildDevContainer();
        $container->get('key');
    }

    public function testClosureIsNotResolved()
    {
        $closure = function () {
            return 'hello';
        };
        $container = ContainerBuilder::buildDevContainer();
        $container->set('key', $closure);
        $this->assertSame($closure, $container->get('key'));
    }

    public function testGetWithClassName()
    {
        $container = ContainerBuilder::buildDevContainer();
        $this->assertInstanceOf('stdClass', $container->get('stdClass'));
    }

    public function testGetWithPrototypeScope()
    {
        $container = ContainerBuilder::buildDevContainer();
        // With @Injectable(scope="prototype") annotation
        $instance1 = $container->get('UnitTests\DI\Fixtures\Prototype');
        $instance2 = $container->get('UnitTests\DI\Fixtures\Prototype');
        $this->assertNotSame($instance1, $instance2);
    }

    public function testGetWithSingletonScope()
    {
        $container = ContainerBuilder::buildDevContainer();
        // Without @Injectable annotation => default is Singleton
        $instance1 = $container->get('stdClass');
        $instance2 = $container->get('stdClass');
        $this->assertSame($instance1, $instance2);
        // With @Injectable(scope="singleton") annotation
        $instance3 = $container->get('UnitTests\DI\Fixtures\Singleton');
        $instance4 = $container->get('UnitTests\DI\Fixtures\Singleton');
        $this->assertSame($instance3, $instance4);
    }

    /**
     * @expectedException \DI\Definition\Exception\DefinitionException
     * @expectedExceptionMessage Error while reading @Injectable on UnitTests\DI\Fixtures\InvalidScope: Value 'foobar' is not part of the enum DI\Scope
     */
    public function testGetWithInvalidScope()
    {
        $container = ContainerBuilder::buildDevContainer();
        $container->get('UnitTests\DI\Fixtures\InvalidScope');
    }

    public function testGetWithProxy()
    {
        $container = ContainerBuilder::buildDevContainer();
        $this->assertInstanceOf('stdClass', $container->get('stdClass', true));
    }

    /**
     * Issue #58
     * @see https://github.com/mnapoli/PHP-DI/issues/58
     */
    public function testGetWithProxyWithAlias()
    {
        $container = ContainerBuilder::buildDevContainer();
        $container->set('foo', Entry::object('stdClass'));
        $this->assertInstanceOf('stdClass', $container->get('foo', true));
    }

    /**
     * Tests if instantiation unlock works. We should be able to create two instances of the same class.
     */
    public function testCircularDependencies()
    {
        $container = ContainerBuilder::buildDevContainer();
        $container->get('UnitTests\DI\Fixtures\Prototype');
        $container->get('UnitTests\DI\Fixtures\Prototype');
    }

    /**
     * @expectedException \DI\DependencyException
     * @expectedExceptionMessage Circular dependency detected while trying to get entry 'UnitTests\DI\Fixtures\Class1CircularDependencies'
     */
    public function testCircularDependencyException()
    {
        $container = ContainerBuilder::buildDevContainer();
        $container->get(Class1CircularDependencies::class);
    }

    /**
     * @expectedException \DI\DependencyException
     * @expectedExceptionMessage Circular dependency detected while trying to get entry 'foo'
     */
    public function testCircularDependencyExceptionWithAlias()
    {
        $container = ContainerBuilder::buildDevContainer();
        // Alias to itself -> infinite recursive loop
        $container->set('foo', Entry::link('foo'));
        $container->get('foo');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The name parameter must be of type string
     */
    public function testGetNonStringParameter()
    {
        $container = ContainerBuilder::buildDevContainer();
        $container->get(new stdClass());
    }

    public function testHas()
    {
        $container = ContainerBuilder::buildDevContainer();
        $container->set('foo', 'bar');

        $this->assertTrue($container->has('foo'));
        $this->assertFalse($container->has('wow'));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The name parameter must be of type string
     */
    public function testHasNonStringParameter()
    {
        $container = ContainerBuilder::buildDevContainer();
        $container->get(new stdClass());
    }

    /**
     * Test that injecting an existing object returns the same reference to that object
     */
    public function testInjectOnMaintainsReferentialEquality()
    {
        $container = ContainerBuilder::buildDevContainer();
        $instance = new stdClass();
        $result = $container->injectOn($instance);

        $this->assertSame($instance, $result);
    }

    /**
     * Test that injection on null yields null
     */
    public function testInjectNull()
    {
        $container = ContainerBuilder::buildDevContainer();
        $result = $container->injectOn(null);

        $this->assertEquals($result, null);
    }

    /**
     * We should be able to set a null value
     * @see https://github.com/mnapoli/PHP-DI/issues/79
     */
    public function testSetNullValue()
    {
        $container = ContainerBuilder::buildDevContainer();
        $container->set('foo', null);

        $this->assertNull($container->get('foo'));
    }

    /**
     * The container auto-registers itself
     */
    public function testContainerIsRegistered()
    {
        $container = ContainerBuilder::buildDevContainer();
        $otherContainer = $container->get('DI\Container');

        $this->assertSame($container, $otherContainer);
    }

    /**
     * @see https://github.com/mnapoli/PHP-DI/issues/126
     * @test
     */
    public function testSetGetSetGet()
    {
        $container = ContainerBuilder::buildDevContainer();

        $container->set('foo', 'bar');
        $container->get('foo');
        $container->set('foo', 'hello');
        
        $this->assertSame('hello', $container->get('foo'));
    }
}
