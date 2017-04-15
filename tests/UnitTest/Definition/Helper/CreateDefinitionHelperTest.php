<?php

namespace DI\Test\UnitTest\Definition\Helper;

use DI\Definition\Helper\CreateDefinitionHelper;
use DI\Scope;

/**
 * @covers \DI\Definition\Helper\CreateDefinitionHelper
 */
class CreateDefinitionHelperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function test_default_config()
    {
        $helper = new CreateDefinitionHelper();
        $definition = $helper->getDefinition('foo');

        $this->assertEquals('foo', $definition->getName());
        $this->assertEquals('foo', $definition->getClassName());
        $this->assertEquals(Scope::SINGLETON, $definition->getScope());
        $this->assertNull($definition->getConstructorInjection());
        $this->assertEmpty($definition->getPropertyInjections());
        $this->assertEmpty($definition->getMethodInjections());
    }

    /**
     * @test
     */
    public function allows_to_define_the_class_name()
    {
        $helper = new CreateDefinitionHelper('bar');
        $definition = $helper->getDefinition('foo');

        $this->assertEquals('foo', $definition->getName());
        $this->assertEquals('bar', $definition->getClassName());
    }

    /**
     * @test
     */
    public function allows_to_define_the_scope()
    {
        $helper = new CreateDefinitionHelper();
        $helper->scope(Scope::PROTOTYPE);
        $definition = $helper->getDefinition('foo');

        $this->assertEquals(Scope::PROTOTYPE, $definition->getScope());
    }

    /**
     * @test
     */
    public function allows_to_declare_the_service_as_lazy()
    {
        $helper = new CreateDefinitionHelper();
        $helper->lazy();
        $definition = $helper->getDefinition('foo');

        $this->assertTrue($definition->isLazy());
    }

    /**
     * @test
     */
    public function allows_to_define_constructor_parameters()
    {
        $helper = new CreateDefinitionHelper();
        $helper->constructor(1, 2, 3);
        $definition = $helper->getDefinition('foo');

        $this->assertEquals([1, 2, 3], $definition->getConstructorInjection()->getParameters());
    }

    /**
     * @test
     */
    public function allows_to_define_a_property_injection()
    {
        $helper = new CreateDefinitionHelper();
        $helper->property('prop', 1);
        $definition = $helper->getDefinition('foo');

        $this->assertCount(1, $definition->getPropertyInjections());
        $propertyInjection = current($definition->getPropertyInjections());
        $this->assertEquals(1, $propertyInjection->getValue());
    }

    /**
     * @test
     */
    public function allows_to_define_a_method_call()
    {
        $helper = new CreateDefinitionHelper();
        $helper->method('method', 1, 2, 3);
        $definition = $helper->getDefinition('foo');

        $this->assertCount(1, $definition->getMethodInjections());
        $methodInjection = current($definition->getMethodInjections());
        $this->assertEquals([1, 2, 3], $methodInjection->getParameters());
    }

    /**
     * @test
     */
    public function allows_to_define_multiple_method_calls()
    {
        $helper = new CreateDefinitionHelper();
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
}
