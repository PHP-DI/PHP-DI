<?php

declare(strict_types=1);

namespace DI\Test\UnitTest\Definition;

use DI\Definition\DecoratorDefinition;
use DI\Definition\ExtendsPreviousDefinition;
use DI\Definition\ValueDefinition;

/**
 * @covers \DI\Definition\DecoratorDefinition
 */
class DecoratorDefinitionTest extends \PHPUnit_Framework_TestCase
{
    public function test_getters()
    {
        $callable = function () {
        };
        $definition = new DecoratorDefinition('foo', $callable);

        $this->assertEquals('foo', $definition->getName());
        $this->assertEquals($callable, $definition->getCallable());
    }

    /**
     * @test
     */
    public function should_accept_callables_other_than_closures()
    {
        $callable = [$this, 'foo'];
        $definition = new DecoratorDefinition('foo', $callable);

        $this->assertEquals('foo', $definition->getName());
        $this->assertEquals($callable, $definition->getCallable());
    }

    /**
     * @test
     */
    public function should_extend_previous_definition()
    {
        $definition = new DecoratorDefinition('foo', function () {
        });
        $this->assertInstanceOf(ExtendsPreviousDefinition::class, $definition);

        $subDefinition = new ValueDefinition('foo', 'bar');
        $definition->setExtendedDefinition($subDefinition);
        $this->assertSame($subDefinition, $definition->getDecoratedDefinition());
    }

    /**
     * @test
     */
    public function should_cast_to_string()
    {
        $this->assertEquals('Decorate(foo)', (string) new DecoratorDefinition('foo', 'bar'));
    }
}
