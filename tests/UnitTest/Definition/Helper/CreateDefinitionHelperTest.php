<?php

declare(strict_types=1);

namespace DI\Test\UnitTest\Definition\Helper;

use DI\Definition\Helper\CreateDefinitionHelper;
use PHPUnit\Framework\TestCase;

/**
 * @covers \DI\Definition\Helper\CreateDefinitionHelper
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\DI\Definition\Helper\CreateDefinitionHelper::class)]
class CreateDefinitionHelperTest extends TestCase
{
    public function test_default_config()
    {
        $helper = new CreateDefinitionHelper();
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
        $helper = new CreateDefinitionHelper('bar');
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
        $helper = new CreateDefinitionHelper();
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
        $helper = new CreateDefinitionHelper();
        $helper->constructor(1, 2, 3);
        $definition = $helper->getDefinition('foo');

        $this->assertEquals([1, 2, 3], $definition->getConstructorInjection()->getParameters());
    }

    /**
     * @test
     */
    #[\PHPUnit\Framework\Attributes\Test]
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
    #[\PHPUnit\Framework\Attributes\Test]
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
    #[\PHPUnit\Framework\Attributes\Test]
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
