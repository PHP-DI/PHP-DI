<?php

declare(strict_types=1);

namespace DI\Test\UnitTest\Definition\Helper;

use DI\Definition\DecoratorDefinition;
use DI\Definition\FactoryDefinition;
use DI\Definition\Helper\FactoryDefinitionHelper;
use PHPUnit\Framework\TestCase;

/**
 * @covers \DI\Definition\Helper\FactoryDefinitionHelper
 */
class FactoryDefinitionHelperTest extends TestCase
{
    /**
     * @test
     */
    public function creates_factory_definition()
    {
        $callable = function () {
        };
        $helper = new FactoryDefinitionHelper($callable);
        $definition = $helper->getDefinition('foo');

        $this->assertTrue($definition instanceof FactoryDefinition);
        $this->assertSame('foo', $definition->getName());
        $this->assertSame($callable, $definition->getCallable());
    }

    /**
     * @test
     */
    public function creates_decorator_definition()
    {
        $callable = function () {
        };
        $helper = new FactoryDefinitionHelper($callable, true);
        $definition = $helper->getDefinition('foo');

        $this->assertTrue($definition instanceof DecoratorDefinition);
        $this->assertSame('foo', $definition->getName());
        $this->assertSame($callable, $definition->getCallable());
    }

    /**
     * @test
     */
    public function allows_to_define_method_parameters()
    {
        $callable = function ($foo) {
        };
        $helper = new FactoryDefinitionHelper($callable);
        $helper->parameter('foo', 'bar');
        $definition = $helper->getDefinition('foo');

        $this->assertEquals($definition->getParameters(), ['foo' => 'bar']);
    }
}
