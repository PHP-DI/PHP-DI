<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace UnitTests\DI;

use DI\ContainerBuilder;
use stdClass;

/**
 * Test class for Container
 *
 * @covers \DI\Container
 */
class ContainerGetTest extends \PHPUnit_Framework_TestCase
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

    /**
     * @coversNothing
     */
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
     * @coversNothing
     */
    public function testGetWithInvalidScope()
    {
        $container = ContainerBuilder::buildDevContainer();
        $container->get('UnitTests\DI\Fixtures\InvalidScope');
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
     * @expectedExceptionMessage Circular dependency detected while trying to resolve entry 'UnitTests\DI\Fixtures\Class1CircularDependencies'
     */
    public function testCircularDependencyException()
    {
        $container = ContainerBuilder::buildDevContainer();
        $container->get('UnitTests\DI\Fixtures\Class1CircularDependencies');
    }

    /**
     * @expectedException \DI\DependencyException
     * @expectedExceptionMessage Circular dependency detected while trying to resolve entry 'foo'
     */
    public function testCircularDependencyExceptionWithAlias()
    {
        $container = ContainerBuilder::buildDevContainer();
        // Alias to itself -> infinite recursive loop
        $container->set('foo', \DI\link('foo'));
        $container->get('foo');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The name parameter must be of type string
     */
    public function testNonStringParameter()
    {
        $container = ContainerBuilder::buildDevContainer();
        $container->get(new stdClass());
    }
}
