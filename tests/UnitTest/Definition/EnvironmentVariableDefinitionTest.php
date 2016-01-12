<?php

namespace DI\Test\UnitTest\Definition;

use DI\Definition\EnvironmentVariableDefinition;
use DI\Scope;

/**
 * @covers \DI\Definition\EnvironmentVariableDefinition
 */
class EnvironmentVariableDefinitionTest extends \PHPUnit_Framework_TestCase
{
    public function test_getters()
    {
        $definition = new EnvironmentVariableDefinition('foo', 'bar', false, 'default');

        $this->assertEquals('foo', $definition->getName());
        $this->assertEquals('bar', $definition->getVariableName());
        $this->assertFalse($definition->isOptional());
        $this->assertEquals('default', $definition->getDefaultValue());
    }

    /**
     * @test
     */
    public function should_have_singleton_scope()
    {
        $definition = new EnvironmentVariableDefinition('foo', 'bar');

        $this->assertEquals(Scope::SINGLETON, $definition->getScope());
    }

    /**
     * @test
     */
    public function should_be_cacheable()
    {
        $this->assertInstanceOf('DI\Definition\CacheableDefinition', new EnvironmentVariableDefinition('foo', 'bar'));
    }
}
