<?php

namespace DI\Test\UnitTest\Definition;

use DI\Definition\ValueDefinition;
use EasyMock\EasyMock;
use Psr\Container\ContainerInterface;

/**
 * @covers \DI\Definition\ValueDefinition
 */
class ValueDefinitionTest extends \PHPUnit_Framework_TestCase
{
    use EasyMock;

    public function test_getters()
    {
        $definition = new ValueDefinition('foo', 1);

        $this->assertEquals('foo', $definition->getName());
        $this->assertEquals(1, $definition->getValue());
    }

    /**
     * @test
     */
    public function should_be_resolvable()
    {
        $definition = new ValueDefinition('foo', 'bar');
        $container = $this->easyMock(ContainerInterface::class);
        $this->assertTrue($definition->isResolvable($container));
    }

    /**
     * @test
     */
    public function should_resolve()
    {
        $definition = new ValueDefinition('foo', 'bar');
        $container = $this->easyMock(ContainerInterface::class);
        $this->assertEquals('bar', $definition->resolve($container));
    }

    public function should_cast_to_string()
    {
        $this->assertEquals("Value ('bar')", (string) new ValueDefinition('', 'bar'));
        $this->assertEquals('Value (3306)', (string) new ValueDefinition('', 3306));
    }
}
