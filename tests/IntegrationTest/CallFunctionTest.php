<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Test\IntegrationTest;

use DI\Container;
use DI\ContainerBuilder;

/**
 * Call functions.
 *
 * @coversNothing
 */
class CallFunctionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Container
     */
    private $container;

    public function setUp()
    {
        $this->container = ContainerBuilder::buildDevContainer();
    }

    public function test_no_parameters()
    {
        $result = $this->container->call(function() {
            return 42;
        });
        $this->assertEquals(42, $result);
    }

    public function test_parameters_ordered()
    {
        $result = $this->container->call(function($foo, $bar) {
            return $foo . $bar;
        }, ['foo', 'bar',]);
        $this->assertEquals('foobar', $result);
    }

    public function test_parameters_indexed_by_name()
    {
        $result = $this->container->call(function($foo, $bar) {
            return $foo . $bar;
        }, [
            'bar' => 'buzz',
            'foo' => 'fizz',
        ]);
        $this->assertEquals('fizzbuzz', $result);
    }

    public function test_parameter_with_definitions_indexed()
    {
        $this->container->set('bar', 'bam');

        $self = $this;
        $result = $this->container->call(function($foo, $bar) use ($self) {
            $self->assertInstanceOf('stdClass', $bar);
            return $foo;
        }, [
            'bar' => \DI\object('stdClass'),
            'foo' => \DI\get('bar'),
        ]);
        $this->assertEquals('bam', $result);
    }

    public function test_parameter_with_definitions_not_indexed()
    {
        $this->container->set('bar', 'bam');

        $self = $this;
        $result = $this->container->call(function($foo, $bar) use ($self) {
            $self->assertInstanceOf('stdClass', $bar);
            return $foo;
        }, [\DI\get('bar'),\DI\object('stdClass')]);
        $this->assertEquals('bam', $result);
    }

    public function test_parameter_default_value()
    {
        $result = $this->container->call(function($foo = 'hello') {
            return $foo;
        });
        $this->assertEquals('hello', $result);
    }

    public function test_parameter_explicit_value_overrides_default_value()
    {
        $result = $this->container->call(function($foo = 'hello') {
            return $foo;
        }, [
            'foo' => 'test',
        ]);
        $this->assertEquals('test', $result);

        $result = $this->container->call(function($foo = 'hello') {
            return $foo;
        }, ['test']);
        $this->assertEquals('test', $result);
    }

    public function test_parameter_from_type_hint()
    {
        $value = new \stdClass();
        $this->container->set('stdClass', $value);

        $result = $this->container->call(function(\stdClass $foo) {
            return $foo;
        });
        $this->assertEquals($value, $result);
    }

    public function test_parameter_from_type_hint_with_root_container()
    {
        $rootContainer = ContainerBuilder::buildDevContainer();
        $value = new \stdClass();
        $rootContainer->set('stdClass', $value);

        $subContainerBuilder = new ContainerBuilder;
        $subContainerBuilder->wrapContainer($rootContainer);
        $subContainer = $subContainerBuilder->build();

        $result = $subContainer->call(function(\stdClass $foo) {
            return $foo;
        });
        $this->assertSame($value, $result, 'The root container was not used for the type-hint');
    }

    /**
     * @test
     */
    public function calls_object_methods()
    {
        $container = ContainerBuilder::buildDevContainer();
        $object = new TestClass();
        $result = $container->call([$object, 'foo']);
        $this->assertEquals(42, $result);
    }

    /**
     * @test
     */
    public function creates_and_calls_class_methods_using_container()
    {
        $class = __NAMESPACE__ . '\TestClass';
        $result = $this->container->call([$class, 'foo']);
        $this->assertEquals(42, $result);
    }

    /**
     * @test
     */
    public function calls_static_methods()
    {
        $class = __NAMESPACE__ . '\TestClass';
        $result = $this->container->call([$class, 'bar']);
        $this->assertEquals(24, $result);
    }

    /**
     * @test
     */
    public function calls_invokable_object()
    {
        $class = __NAMESPACE__ . '\CallableTestClass';
        $result = $this->container->call(new $class);
        $this->assertEquals(42, $result);
    }

    /**
     * @test
     */
    public function creates_and_calls_invokable_objects_using_container()
    {
        $result = $this->container->call(__NAMESPACE__ . '\CallableTestClass');
        $this->assertEquals(42, $result);
    }

    /**
     * @test
     */
    public function calls_functions()
    {
        $result = $this->container->call(__NAMESPACE__ . '\CallFunctionTest_function', [
            'str' => 'foo',
        ]);
        $this->assertEquals(3, $result);
    }

    /**
     * @expectedException \Invoker\Exception\NotEnoughParametersException
     * @expectedExceptionMessage Unable to invoke the callable because no value was given for parameter 1 ($foo)
     */
    public function test_not_enough_parameters()
    {
        $this->container->call(function($foo) {});
    }

    /**
     * @expectedException \Invoker\Exception\NotCallableException
     * @expectedExceptionMessage foo is neither a callable or a valid container entry
     */
    public function test_not_callable()
    {
        $this->container->call('foo');
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

function CallFunctionTest_function($str) {
    return strlen($str);
}
