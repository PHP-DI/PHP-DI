<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.io/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace UnitTests\DI;

use DI\Container;
use DI\ContainerBuilder;
use DI\Definition\DefinitionManager;
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

    public function testHasSingletonDoesNotChangeContainer() {
        $container = ContainerBuilder::buildDevContainer();
        $class_name = 'UnitTests\DI\Fixtures\Singleton';

        $this->assertFalse($container->hasSingleton($class_name));
        $this->assertFalse($container->hasSingleton($class_name));
    }

    public function testHasSingletonDoesSeeSetSingletons() {
        $container = ContainerBuilder::buildDevContainer();
        $this->assertFalse($container->hasSingleton('stdClass'));
        $container->set('stdClass', new StdClass());
        $this->assertTrue($container->hasSingleton('stdClass'));
    }

    public function testHasSingletonDoeSeeFetchedSingletons() {
        $container = ContainerBuilder::buildDevContainer();

        $class_name = 'UnitTests\DI\Fixtures\Singleton';

        $this->assertFalse($container->hasSingleton($class_name));
        $container->get($class_name);
        $this->assertTrue($container->hasSingleton($class_name));
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

    public function testGetDefinitionManager()
    {
        $definitionManager = new DefinitionManager();
        $proxyFactory = $this->getMock('ProxyManager\Factory\LazyLoadingValueHolderFactory');
        $container = new Container($definitionManager, $proxyFactory);

        $this->assertSame($definitionManager, $container->getDefinitionManager());
    }
}
