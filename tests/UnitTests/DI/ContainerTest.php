<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.io/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace UnitTests\DI;

use stdClass;
use DI\Container;

/**
 * Test class for Container
 */
class ContainerTest extends \PHPUnit_Framework_TestCase
{

    public function testSetGet()
    {
        $container = new Container();
        $dummy = new stdClass();
        $container->set('key', $dummy);
        $this->assertSame($dummy, $container->get('key'));
    }

    /**
     * @expectedException \DI\NotFoundException
     */
    public function testGetNotFound()
    {
        $container = new Container();
        $container->get('key');
    }

    public function testGetWithClosure()
    {
        $container = new Container();
        $container->set(
            'key',
            function () {
                return 'hello';
            }
        );
        $this->assertEquals('hello', $container->get('key'));
    }

    public function testGetWithClosureIsCached()
    {
        $container = new Container();
        $container->set(
            'key',
            function () {
                return new stdClass();
            }
        );
        $instance1 = $container->get('key');
        $instance2 = $container->get('key');
        $this->assertSame($instance1, $instance2);
    }

    public function testGetWithClassName()
    {
        $container = new Container();
        $this->assertInstanceOf('stdClass', $container->get('stdClass'));
    }

    public function testGetWithPrototypeScope()
    {
        $container = new Container();
        // With @Injectable(scope="prototype") annotation
        $instance1 = $container->get('UnitTests\DI\Fixtures\Prototype');
        $instance2 = $container->get('UnitTests\DI\Fixtures\Prototype');
        $this->assertNotSame($instance1, $instance2);
    }

    public function testGetWithSingletonScope()
    {
        $container = new Container();
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
        $container = new Container();
        $container->get('UnitTests\DI\Fixtures\InvalidScope');
    }

    public function testGetWithProxy()
    {
        $container = new Container();
        $this->assertInstanceOf('stdClass', $container->get('stdClass', true));
    }

    /**
     * Issue #58
     * @see https://github.com/mnapoli/PHP-DI/issues/58
     */
    public function testGetWithProxyWithAlias()
    {
        $container = new Container();
        $container->addDefinitions(
            array(
                'foo' => array(
                    'class' => 'stdClass',
                ),
            )
        );
        $this->assertInstanceOf('stdClass', $container->get('foo', true));
    }

    /**
     * Tests if instantiation unlock works. We should be able to create two instances of the same class.
     */
    public function testCircularDependencies()
    {
        $container = new Container();
        $container->get('UnitTests\DI\Fixtures\Prototype');
        $container->get('UnitTests\DI\Fixtures\Prototype');
    }

    /**
     * @expectedException \DI\DependencyException
     * @expectedExceptionMessage Circular dependency detected while trying to instantiate class 'UnitTests\DI\Fixtures\Class1CircularDependencies'
     */
    public function testCircularDependenciesException()
    {
        $container = new Container();
        $container->get('UnitTests\DI\Fixtures\Class1CircularDependencies');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The name parameter must be of type string
     */
    public function testGetNonStringParameter()
    {
        $container = new Container();
        $container->get(new stdClass());
    }

    /**
     * Test that injecting an existing object returns the same reference to that object
     */
    public function testInjectMaintainsReferentialEquality()
    {
        $container = new Container();
        $instance = new stdClass();
        $result = $container->inject($instance);

        $this->assertSame($instance, $result);
    }

    /**
     * Test that injection on null yields null
     */
    public function testInjectNull()
    {
        $container = new Container();
        $result = $container->inject(null);

        $this->assertEquals($result, null);
    }

    /**
     * We should be able to set a null value
     * @see https://github.com/mnapoli/PHP-DI/issues/79
     */
    public function testSetNullValue()
    {
        $container = new Container();
        $return = $container->set('foo', null);

        $this->assertNull($return);
        $this->assertNull($container->get('foo'));
    }

    /**
     * @see https://github.com/mnapoli/PHP-DI/issues/79
     * @test
     */
    public function setWithoutValueShouldReturnClassDefinitionHelper()
    {
        $container = new Container();
        $return = $container->set('foo');

        $this->assertInstanceOf('DI\Definition\Helper\ClassDefinitionHelper', $return);
    }

    /**
     * The container auto-registers itself
     */
    public function testContainerIsRegistered()
    {
        $container = new Container();
        $otherContainer = $container->get('DI\Container');

        $this->assertSame($container, $otherContainer);
    }

}
