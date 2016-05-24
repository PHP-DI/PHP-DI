<?php

namespace DI\Test\UnitTest\Definition;

use DI\Definition\CacheableDefinition;
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
        $this->assertInstanceOf(CacheableDefinition::class, new EnvironmentVariableDefinition('foo', 'bar'));
    }

    /**
     * @test
     */
    public function should_cast_to_string()
    {
        $str = 'Environment variable (
    variable = bar
    optional = no
)';
        $this->assertEquals($str, (string) new EnvironmentVariableDefinition('', 'bar'));
    }

    /**
     * @test
     */
    public function should_cast_to_string_with_default_value()
    {
        $str = 'Environment variable (
    variable = bar
    optional = yes
    default = \'<default>\'
)';
        $this->assertEquals($str, (string) new EnvironmentVariableDefinition('', 'bar', true, '<default>'));
    }

    /**
     * @test
     */
    public function should_cast_to_string_with_reference_as_default_value()
    {
        $str = 'Environment variable (
    variable = bar
    optional = yes
    default = get(foo)
)';
        $this->assertEquals($str, (string) new EnvironmentVariableDefinition('', 'bar', true, \DI\get('foo')));
    }

    /**
     * @test
     */
    public function should_cast_to_string_with_nested_definition_as_default_value()
    {
        $str = 'Environment variable (
    variable = bar
    optional = yes
    default = Environment variable (
        variable = foo
        optional = no
    )
)';
        $this->assertEquals($str, (string) new EnvironmentVariableDefinition('', 'bar', true, \DI\env('foo')));
    }
}
