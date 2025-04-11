<?php

declare(strict_types=1);

namespace DI\Test\UnitTest\Definition\Helper;

use DI\Definition\Helper\AutowireDefinitionHelper;
use DI\Definition\ObjectDefinition\MethodInjection;
use DI\Test\UnitTest\Definition\Helper\Fixtures\Class1;
use PHPUnit\Framework\TestCase;
use DI\Definition\Exception\InvalidDefinition;

/**
 * @covers \DI\Definition\Helper\AutowireDefinitionHelper
 */
class AutowireDefinitionHelperTest extends TestCase
{
    public function test_default_config()
    {
        $helper = new AutowireDefinitionHelper();
        $definition = $helper->getDefinition('foo');

        $this->assertEquals('foo', $definition->getName());
        $this->assertEquals('foo', $definition->getClassName());
        $this->assertNull($definition->getConstructorInjection());
        $this->assertEmpty($definition->getPropertyInjections());
        $this->assertEmpty($definition->getMethodInjections());
    }

    /**
     * @test
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function allows_to_define_the_class_name()
    {
        $helper = new AutowireDefinitionHelper('bar');
        $definition = $helper->getDefinition('foo');

        $this->assertEquals('foo', $definition->getName());
        $this->assertEquals('bar', $definition->getClassName());
    }

    /**
     * @test
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function allows_to_declare_the_service_as_lazy()
    {
        $helper = new AutowireDefinitionHelper();
        $helper->lazy();
        $definition = $helper->getDefinition('foo');

        $this->assertTrue($definition->isLazy());
    }

    /**
     * @test
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function allows_to_define_constructor_parameters()
    {
        $helper = new AutowireDefinitionHelper();
        $helper->constructor(1, 2, 3);
        $definition = $helper->getDefinition('foo');

        $this->assertEquals([1, 2, 3], $definition->getConstructorInjection()->getParameters());
    }

    /**
     * @test
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function allows_to_override_a_parameter_injection()
    {
        $helper = new AutowireDefinitionHelper();
        $helper->constructorParameter(0, 42);
        $definition = $helper->getDefinition('foo');

        $constructorInjection = $definition->getConstructorInjection();

        $this->assertEquals([42], $constructorInjection->getParameters());
    }

    /**
     * @test
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function allows_to_define_a_property_injection()
    {
        $helper = new AutowireDefinitionHelper();
        $helper->property('prop', 1);
        $definition = $helper->getDefinition('foo');

        $this->assertCount(1, $definition->getPropertyInjections());
        $propertyInjection = current($definition->getPropertyInjections());
        $this->assertEquals(1, $propertyInjection->getValue());
    }

    /**
     * @test
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function allows_to_define_a_method_call()
    {
        $helper = new AutowireDefinitionHelper();
        $helper->method('method', 1, 2, 3);
        $definition = $helper->getDefinition('foo');

        $this->assertCount(1, $definition->getMethodInjections());
        $methodInjection = current($definition->getMethodInjections());
        $this->assertEquals([1, 2, 3], $methodInjection->getParameters());
    }

    /**
     * @test
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function allows_to_define_multiple_method_calls()
    {
        $helper = new AutowireDefinitionHelper();
        $helper->method('method', 1, 2);
        $helper->method('method', 3, 4);
        $definition = $helper->getDefinition('foo');

        $methodCalls = $definition->getMethodInjections();
        $this->assertCount(2, $methodCalls);
        $methodInjection = array_shift($methodCalls);
        $this->assertEquals([1, 2], $methodInjection->getParameters());
        $methodInjection = array_shift($methodCalls);
        $this->assertEquals([3, 4], $methodInjection->getParameters());
    }

    /**
     * @test
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function allows_to_override_a_parameter_injection_by_index()
    {
        $helper = new AutowireDefinitionHelper();
        $helper->methodParameter('method', 0, 42);
        $definition = $helper->getDefinition('foo');

        $this->assertCount(1, $definition->getMethodInjections());
        /** @var MethodInjection $methodInjection */
        $methodInjection = current($definition->getMethodInjections());

        $this->assertEquals('method', $methodInjection->getMethodName());
        $this->assertEquals([42], $methodInjection->getParameters());
    }

    /**
     * Check using the parameter name, not its index.
     * @test
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function allows_to_override_a_parameter_injection_by_name()
    {
        $helper = new AutowireDefinitionHelper();
        $helper->methodParameter('method', 'param2', 'val2');
        $helper->methodParameter('method', 'param1', 'val1');
        $definition = $helper->getDefinition(Class1::class);

        $this->assertCount(1, $definition->getMethodInjections());
        $methodInjection = current($definition->getMethodInjections());

        // Check that injections are in the good order (matching the real parameters order)
        $this->assertEquals(['val1', 'val2'], $methodInjection->getParameters());
    }

    /**
     * If using methodParameter() for "__construct", then the constructor definition should be updated.
     * @test
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function should_update_constructor_definition_if_overriding_parameter_for_constructor()
    {
        $helper = new AutowireDefinitionHelper();
        $helper->methodParameter('__construct', 0, 42);
        $definition = $helper->getDefinition('foo');

        $this->assertCount(0, $definition->getMethodInjections());
        $this->assertNotNull($definition->getConstructorInjection());

        $this->assertEquals([42], $definition->getConstructorInjection()->getParameters());
    }

    public function test_error_message_on_unknown_parameter()
    {
        $this->expectException(InvalidDefinition::class);
        $this->expectExceptionMessage('Parameter with name \'wrongName\' could not be found');
        $helper = new AutowireDefinitionHelper();
        $helper->methodParameter('__construct', 'wrongName', 42);
        $helper->getDefinition(Class1::class);
    }
}
