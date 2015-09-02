<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Test\UnitTest;

use DI\ContainerBuilder;
use stdClass;

/**
 * Test class for Container
 *
 * @covers \DI\Container
 */
class ContainerTest extends \PHPUnit_Framework_TestCase
{
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
        $container->has(new stdClass());
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

        $this->assertSame($container, $container->get('DI\Container'));
    }

    /**
     * The container auto-registers itself (with the factory interface)
     */
    public function testFactoryInterfaceIsRegistered()
    {
        $container = ContainerBuilder::buildDevContainer();

        $this->assertSame($container, $container->get('DI\FactoryInterface'));
    }

    /**
     * The container auto-registers itself (with the invoker interface)
     */
    public function testInvokerInterfaceIsRegistered()
    {
        $container = ContainerBuilder::buildDevContainer();

        $this->assertSame($container, $container->get('DI\InvokerInterface'));
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
