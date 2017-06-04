<?php

declare(strict_types=1);

namespace DI\Test\UnitTest\Definition;

use DI\Definition\FactoryDefinition;

/**
 * @covers \DI\Definition\FactoryDefinition
 */
class FactoryDefinitionTest extends \PHPUnit_Framework_TestCase
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
    public function should_cast_to_string()
    {
        $this->assertEquals('Factory', new FactoryDefinition('', 'bar'));
    }

    /**
     * @test
     */
    public function should_accept_parameters()
    {
        $parameters = ['flag' => true];
        $definition = new FactoryDefinition('foo', function () {
        }, $parameters);

        $this->assertEquals($parameters, $definition->getParameters());
    }
}
