<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Test\UnitTest\Definition\Resolver;

use DI\Definition\FunctionCallDefinition;
use DI\Definition\Resolver\AliasResolver;
use DI\Definition\Resolver\FunctionInvoker;
use DI\Definition\ValueDefinition;
use EasyMock\EasyMock;
use Interop\Container\ContainerInterface;
use PHPUnit_Framework_MockObject_MockObject;

/**
 * @covers \DI\Definition\Resolver\FunctionInvoker
 * @covers \DI\Definition\Resolver\ParameterResolver
 */
class FunctionInvokerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContainerInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $container;

    /**
     * @var FunctionInvoker
     */
    private $resolver;

    public function setUp()
    {
        $this->container = EasyMock::mock('Interop\Container\ContainerInterface');
        $resolver = new AliasResolver($this->container);
        $this->resolver = new FunctionInvoker($this->container, $resolver);
    }

    /**
     * @test
     */
    public function should_call_closure()
    {
        $definition = $this->definition(function () {
            return 42;
        });

        $this->assertEquals(42, $this->resolver->resolve($definition));
    }

    /**
     * @test
     */
    public function should_call_closure_with_parameters()
    {
        $this->assert_container_get($this->container, 'bar', 42);

        $definition = $this->definition(function ($foo, $bar) {
            return array($foo, $bar);
        }, array('foo', \DI\get('bar')));

        $this->assertEquals(array('foo', 42), $this->resolver->resolve($definition));
    }

    /**
     * @test
     */
    public function should_call_object_method()
    {
        $definition = $this->definition(array(new TestClass(), 'foo'));

        $this->assertEquals(42, $this->resolver->resolve($definition));
    }

    /**
     * @test
     */
    public function should_call_class_method()
    {
        $class = __NAMESPACE__ . '\TestClass';

        // It should instantiate the class with Container::get()
        $this->assert_container_get($this->container, $class, new TestClass());

        $definition = $this->definition(array($class, 'foo'));

        $this->assertEquals(42, $this->resolver->resolve($definition));
    }

    /**
     * @test
     */
    public function should_call_static_method()
    {
        $class = __NAMESPACE__ . '\TestClass';

        // It should NOT instantiate the class with Container::get()
        $this->container->expects($this->never())->method('get');

        $definition = $this->definition(array($class, 'bar'));

        $this->assertEquals(24, $this->resolver->resolve($definition));
    }

    /**
     * @test
     */
    public function should_call_callable_object()
    {
        $definition = $this->definition(new CallableTestClass());

        $this->assertEquals(42, $this->resolver->resolve($definition));
    }

    /**
     * @test
     */
    public function should_call_callable_class()
    {
        $class = __NAMESPACE__ . '\CallableTestClass';

        // It should instantiate the class with Container::get()
        $this->assert_container_get($this->container, $class, new CallableTestClass());

        $definition = $this->definition($class);

        $this->assertEquals(42, $this->resolver->resolve($definition));
    }

    /**
     * @test
     */
    public function should_call_function()
    {
        $definition = $this->definition(__NAMESPACE__ . '\FunctionCallDefinitionResolverTest_function');

        $this->assertEquals(3, $this->resolver->resolve($definition, array('str' => 'foo')));
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage This definition resolver is only compatible with FunctionCallDefinition objects, DI\Definition\ValueDefinition given
     */
    public function should_only_resolve_function_call_definitions()
    {
        $this->resolver->resolve(new ValueDefinition('foo', 'bar'));
    }

    private function assert_container_get(\PHPUnit_Framework_MockObject_MockObject $container, $id, $returnedValue)
    {
        $container->expects($this->once())
            ->method('get')
            ->with($id)
            ->will($this->returnValue($returnedValue));
    }

    private function definition($callable, array $parameters = array())
    {
        return new FunctionCallDefinition($callable, $parameters);
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

function FunctionCallDefinitionResolverTest_function($str) {
    return strlen($str);
}
