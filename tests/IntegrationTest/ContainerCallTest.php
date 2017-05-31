<?php

namespace DI\Test\IntegrationTest;

use DI\ContainerBuilder;

/**
 * Tests the call() method from the container.
 */
class ContainerCallTest extends BaseContainerTest
{
    /**
     * @dataProvider provideContainer
     */
    public function test_no_parameters(ContainerBuilder $builder)
    {
        $result = $builder->build()->call(function () {
            return 42;
        });
        $this->assertEquals(42, $result);
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_parameters_ordered(ContainerBuilder $builder)
    {
        $result = $builder->build()->call(function ($foo, $bar) {
            return $foo . $bar;
        }, ['foo', 'bar']);
        $this->assertEquals('foobar', $result);
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_parameters_indexed_by_name(ContainerBuilder $builder)
    {
        $result = $builder->build()->call(function ($foo, $bar) {
            return $foo . $bar;
        }, [
            // Reverse order: should still work
            'bar' => 'buzz',
            'foo' => 'fizz',
        ]);
        $this->assertEquals('fizzbuzz', $result);
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_parameter_with_definitions_indexed(ContainerBuilder $builder)
    {
        $container = $builder->build();
        $container->set('bar', 'bam');

        $self = $this;
        $result = $container->call(function ($foo, $bar) use ($self) {
            $self->assertInstanceOf('stdClass', $bar);

            return $foo;
        }, [
            'bar' => \DI\create('stdClass'),
            'foo' => \DI\get('bar'),
        ]);
        $this->assertEquals('bam', $result);
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_parameter_with_definitions_not_indexed(ContainerBuilder $builder)
    {
        $container = $builder->build();
        $container->set('bar', 'bam');

        $self = $this;
        $result = $container->call(function ($foo, $bar) use ($self) {
            $self->assertInstanceOf('stdClass', $bar);

            return $foo;
        }, [\DI\get('bar'), \DI\create('stdClass')]);
        $this->assertEquals('bam', $result);
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_parameter_default_value(ContainerBuilder $builder)
    {
        $result = $builder->build()->call(function ($foo = 'hello') {
            return $foo;
        });
        $this->assertEquals('hello', $result);
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_parameter_explicit_value_overrides_default_value(ContainerBuilder $builder)
    {
        $container = $builder->build();

        $result = $container->call(function ($foo = 'hello') {
            return $foo;
        }, [
            'foo' => 'test',
        ]);
        $this->assertEquals('test', $result);

        $result = $container->call(function ($foo = 'hello') {
            return $foo;
        }, ['test']);
        $this->assertEquals('test', $result);
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_parameter_from_type_hint(ContainerBuilder $builder)
    {
        $container = $builder->build();

        $value = new \stdClass();
        $container->set('stdClass', $value);

        $result = $container->call(function (\stdClass $foo) {
            return $foo;
        });
        $this->assertEquals($value, $result);
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_parameter_from_type_hint_with_root_container(ContainerBuilder $subContainerBuilder)
    {
        $rootContainer = ContainerBuilder::buildDevContainer();
        $value = new \stdClass();
        $rootContainer->set('stdClass', $value);

        $subContainerBuilder->wrapContainer($rootContainer);
        $subContainer = $subContainerBuilder->build();

        $result = $subContainer->call(function (\stdClass $foo) {
            return $foo;
        });
        $this->assertSame($value, $result, 'The root container was not used for the type-hint');
    }

    /**
     * @test
     * @dataProvider provideContainer
     */
    public function calls_object_methods(ContainerBuilder $builder)
    {
        $object = new TestClass();
        $result = $builder->build()->call([$object, 'foo']);
        $this->assertEquals(42, $result);
    }

    /**
     * @test
     * @dataProvider provideContainer
     */
    public function creates_and_calls_class_methods_using_container(ContainerBuilder $builder)
    {
        $class = __NAMESPACE__ . '\TestClass';
        $result = $builder->build()->call([$class, 'foo']);
        $this->assertEquals(42, $result);
    }

    /**
     * @test
     * @dataProvider provideContainer
     */
    public function calls_static_methods(ContainerBuilder $builder)
    {
        $class = __NAMESPACE__ . '\TestClass';
        $result = $builder->build()->call([$class, 'bar']);
        $this->assertEquals(24, $result);
    }

    /**
     * @test
     * @dataProvider provideContainer
     */
    public function calls_invokable_object(ContainerBuilder $builder)
    {
        $class = __NAMESPACE__ . '\CallableTestClass';
        $result = $builder->build()->call(new $class);
        $this->assertEquals(42, $result);
    }

    /**
     * @test
     * @dataProvider provideContainer
     */
    public function creates_and_calls_invokable_objects_using_container(ContainerBuilder $builder)
    {
        $result = $builder->build()->call(__NAMESPACE__ . '\CallableTestClass');
        $this->assertEquals(42, $result);
    }

    /**
     * @test
     * @dataProvider provideContainer
     */
    public function calls_functions(ContainerBuilder $builder)
    {
        $result = $builder->build()->call(__NAMESPACE__ . '\CallFunctionTest_function', [
            'str' => 'foo',
        ]);
        $this->assertEquals(3, $result);
    }

    /**
     * @dataProvider provideContainer
     * @expectedException \Invoker\Exception\NotEnoughParametersException
     * @expectedExceptionMessage Unable to invoke the callable because no value was given for parameter 1 ($foo)
     */
    public function test_not_enough_parameters(ContainerBuilder $builder)
    {
        $builder->build()->call(function ($foo) {
        });
    }

    /**
     * @dataProvider provideContainer
     * @expectedException \Invoker\Exception\NotCallableException
     * @expectedExceptionMessage 'foo' is neither a callable nor a valid container entry
     */
    public function test_not_callable(ContainerBuilder $builder)
    {
        $builder->build()->call('foo');
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

function CallFunctionTest_function($str)
{
    return strlen($str);
}
