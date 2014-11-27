<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Test\UnitTest\Definition\Resolver;

use DI\Container;
use DI\Definition\FunctionCallDefinition;
use DI\Definition\Resolver\FunctionCallDefinitionResolver;
use DI\Definition\ValueDefinition;

/**
 * @covers \DI\Definition\Resolver\FunctionCallDefinitionResolver
 * @covers \DI\Definition\Resolver\ParameterResolver
 */
class FunctionCallDefinitionResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function call_closure()
    {
        $resolver = $this->assert_definition_resolver($this->assert_container());

        $definition = $this->definition(function () {
            return 42;
        });

        $this->assertEquals(42, $resolver->resolve($definition));
    }

    /**
     * @test
     */
    public function call_closure_with_parameters()
    {
        $container = $this->assert_container();
        $resolver = $this->assert_definition_resolver($container);

        $this->assert_container_get($container, 'bar', 42);

        $definition = $this->definition(function ($foo, $bar) {
            return array($foo, $bar);
        }, array('foo', \DI\link('bar')));

        $this->assertEquals(array('foo', 42), $resolver->resolve($definition));
    }

    /**
     * @test
     */
    public function call_object_method()
    {
        $resolver = $this->assert_definition_resolver($this->assert_container());

        $definition = $this->definition(array(new TestClass(), 'foo'));

        $this->assertEquals(42, $resolver->resolve($definition));
    }

    /**
     * @test
     */
    public function call_class_method()
    {
        $container = $this->assert_container();
        $resolver = $this->assert_definition_resolver($container);
        $class = __NAMESPACE__ . '\TestClass';

        // It should instantiate the class with Container::get()
        $this->assert_container_get($container, $class, new TestClass());

        $definition = $this->definition(array($class, 'foo'));

        $this->assertEquals(42, $resolver->resolve($definition));
    }

    /**
     * @test
     */
    public function call_static_method()
    {
        $container = $this->assert_container();
        $resolver = $this->assert_definition_resolver($container);
        $class = __NAMESPACE__ . '\TestClass';

        // It should NOT instantiate the class with Container::get()
        $container->expects($this->never())->method('get');

        $definition = $this->definition(array($class, 'bar'));

        $this->assertEquals(24, $resolver->resolve($definition));
    }

    /**
     * @test
     */
    public function call_callable_object()
    {
        $resolver = $this->assert_definition_resolver($this->assert_container());

        $definition = $this->definition(new CallableTestClass());

        $this->assertEquals(42, $resolver->resolve($definition));
    }

    /**
     * @test
     */
    public function call_callable_class()
    {
        $container = $this->assert_container();
        $resolver = $this->assert_definition_resolver($container);
        $class = __NAMESPACE__ . '\CallableTestClass';

        // It should instantiate the class with Container::get()
        $this->assert_container_get($container, $class, new CallableTestClass());

        $definition = $this->definition($class);

        $this->assertEquals(42, $resolver->resolve($definition));
    }

    /**
     * @test
     */
    public function call_function()
    {
        $resolver = $this->assert_definition_resolver($this->assert_container());

        $definition = $this->definition(__NAMESPACE__ . '\FunctionCallDefinitionResolverTest_function');

        $this->assertEquals(3, $resolver->resolve($definition, array('str' => 'foo')));
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage This definition resolver is only compatible with FunctionCallDefinition objects, DI\Definition\ValueDefinition given
     */
    public function call_invalid_definition()
    {
        $resolver = $this->assert_definition_resolver($this->assert_container());

        $resolver->resolve(new ValueDefinition('foo', 'bar'));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Container
     */
    private function assert_container()
    {
        return $this->getMock('DI\Container', array(), array(), '', false);
    }

    private function assert_definition_resolver(Container $container)
    {
        return new FunctionCallDefinitionResolver($container);
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
