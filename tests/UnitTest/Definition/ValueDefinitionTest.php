<?php

declare(strict_types=1);

namespace DI\Test\UnitTest\Definition;

use DI\Definition\ValueDefinition;
use EasyMock\EasyMock;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * @covers \DI\Definition\ValueDefinition
 */
class ValueDefinitionTest extends TestCase
{
    use EasyMock;

    public function test_getters()
    {
        $definition = new ValueDefinition(1);
        $definition->setName('foo');

        $this->assertEquals('foo', $definition->getName());
        $this->assertEquals(1, $definition->getValue());
    }

    /**
     * @test
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function should_be_resolvable()
    {
        $definition = new ValueDefinition('foo');
        $container = $this->easyMock(ContainerInterface::class);
        $this->assertTrue($definition->isResolvable($container));
    }

    /**
     * @test
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function should_resolve()
    {
        $definition = new ValueDefinition('bar');
        $container = $this->easyMock(ContainerInterface::class);
        $this->assertEquals('bar', $definition->resolve($container));
    }

    /**
     * @test
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function should_cast_to_string()
    {
        $this->assertEquals("Value ('bar')", (string) new ValueDefinition('bar'));
        $this->assertEquals('Value (3306)', (string) new ValueDefinition(3306));
    }
}
