<?php

namespace DI\Test\UnitTest\Definition;

use DI\Definition\FactoryDefinition;
use DI\Scope;

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
        // Default scope
        $this->assertEquals(Scope::SINGLETON, $definition->getScope());
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
    public function can_have_a_custom_scope()
    {
        $definition = new FactoryDefinition('foo', function () {
        }, Scope::PROTOTYPE);

        $this->assertEquals(Scope::PROTOTYPE, $definition->getScope());
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
        }, null, $parameters);

        $this->assertEquals($parameters, $definition->getParameters());
    }
}
