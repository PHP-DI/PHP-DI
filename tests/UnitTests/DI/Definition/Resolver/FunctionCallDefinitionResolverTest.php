<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.io/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace UnitTests\DI\Definition\Resolver;

use DI\Definition\FunctionCallDefinition;
use DI\Definition\Resolver\FunctionCallDefinitionResolver;
use DI\Definition\ValueDefinition;

/**
 * @covers \DI\Definition\Resolver\FunctionCallDefinitionResolver
 */
class FunctionCallDefinitionResolverTest extends \PHPUnit_Framework_TestCase
{
    public function testSimpleResolve()
    {
        $container = $this->getMock('DI\Container', array(), array(), '', false);

        $definition = new FunctionCallDefinition(function () {
            return 42;
        });
        $resolver = new FunctionCallDefinitionResolver($container);

        $this->assertEquals(42, $resolver->resolve($definition));
    }

    public function testResolveWithContainerEntries()
    {
        $container = $this->getMock('DI\Container', array(), array(), '', false);
        $container->expects($this->once())
            ->method('get')
            ->with('bar')
            ->will($this->returnValue(42));

        $definition = new FunctionCallDefinition(function ($foo, $bar) {
            return array($foo, $bar);
        }, array('foo', \DI\link('bar')));
        $resolver = new FunctionCallDefinitionResolver($container);

        $value = $resolver->resolve($definition);

        $this->assertEquals(array('foo', 42), $value);
    }

    public function testResolveMethodCall()
    {
        $container = $this->getMock('DI\Container', array(), array(), '', false);

        $object = new TestClass();
        $definition = new FunctionCallDefinition(array($object, 'foo'));
        $resolver = new FunctionCallDefinitionResolver($container);

        $this->assertEquals(42, $resolver->resolve($definition));
    }

    public function testResolveStringMethodNonStaticCall()
    {
        $class = __NAMESPACE__ . '\TestClass';

        $container = $this->getMock('DI\Container', array(), array(), '', false);
        $container->expects($this->once())
            ->method('get')
            ->with($class)
            ->will($this->returnValue(new $class()));

        $definition = new FunctionCallDefinition(array($class, 'foo'));
        $resolver = new FunctionCallDefinitionResolver($container);

        $this->assertEquals(42, $resolver->resolve($definition));
    }

    public function testResolveStringMethodStaticCall()
    {
        $class = __NAMESPACE__ . '\TestClass';

        $container = $this->getMock('DI\Container', array(), array(), '', false);

        $definition = new FunctionCallDefinition(array($class, 'bar'));
        $resolver = new FunctionCallDefinitionResolver($container);

        $this->assertEquals(24, $resolver->resolve($definition));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage This definition resolver is only compatible with FunctionCallDefinition objects, DI\Definition\ValueDefinition given
     */
    public function testInvalidDefinitionType()
    {
        /** @var \DI\Container $container */
        $container = $this->getMock('DI\Container', array(), array(), '', false);

        $definition = new ValueDefinition('foo', 'bar');
        $resolver = new FunctionCallDefinitionResolver($container);

        $resolver->resolve($definition);
    }

    public function testResolveCallableObject()
    {
        $container = $this->getMock('DI\Container', array(), array(), '', false);

        $definition = new FunctionCallDefinition(new CallableTestClass());
        $resolver = new FunctionCallDefinitionResolver($container);

        $this->assertEquals(42, $resolver->resolve($definition));
    }
}

class TestClass
{
    public function foo()
    {
        return 42;
    }

    public static function bar()
    {
        return 24;
    }
}

class CallableTestClass
{
    public function __invoke()
    {
        return 42;
    }
}
