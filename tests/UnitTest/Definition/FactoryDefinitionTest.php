<?php

declare(strict_types=1);

namespace DI\Test\UnitTest\Definition;

use DI\Definition\FactoryDefinition;
use PHPUnit\Framework\TestCase;

/**
 * @covers \DI\Definition\FactoryDefinition
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\DI\Definition\FactoryDefinition::class)]
class FactoryDefinitionTest extends TestCase
{
    public function test_getters()
    {
        $callable = function () {
        };
        $definition = new FactoryDefinition('foo', $callable);

        $this->assertEquals('foo', $definition->getName());
        $this->assertEquals($callable, $definition->getCallable());
        // Default parameters
        $this->assertEquals([], $definition->getParameters());
    }

    /**
     * @test
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function should_accept_callables_other_than_closures()
    {
        $callable = [$this, 'foo'];
        $definition = new FactoryDefinition('foo', $callable);

        $this->assertEquals('foo', $definition->getName());
        $this->assertEquals($callable, $definition->getCallable());
    }

    /**
     * @test
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function should_cast_to_string()
    {
        $this->assertEquals('Factory', new FactoryDefinition('', 'bar'));
    }

    /**
     * @test
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function should_accept_parameters()
    {
        $parameters = ['flag' => true];
        $definition = new FactoryDefinition('foo', function () {
        }, $parameters);

        $this->assertEquals($parameters, $definition->getParameters());
    }
}
