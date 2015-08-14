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
    /**
     * @test
     */
    public function has_environment_variable()
    {
        $definition = new EnvironmentVariableDefinition('foo');
        $this->assertEquals('foo', $definition->getVariableName());
    }

    /**
     * @test
     */
    public function has_name()
    {
        $definition = new EnvironmentVariableDefinition('foo');
        $definition->setName('bar');

        $this->assertEquals('bar', $definition->getName());
    }

    /**
     * @test
     */
    public function is_mandatory_by_default()
    {
        $definition = new EnvironmentVariableDefinition('foo');
        $this->assertFalse($definition->isOptional());
    }

    /**
     * @test
     */
    public function can_be_optional()
    {
        $definition = new EnvironmentVariableDefinition('foo', true, 'default');

        $this->assertTrue($definition->isOptional());
        $this->assertEquals('default', $definition->getDefaultValue());
    }

    /**
     * @test
     */
    public function has_singleton_scope()
    {
        $definition = new EnvironmentVariableDefinition('foo', 'bar');

        $this->assertEquals(Scope::SINGLETON, $definition->getScope());
    }

    /**
     * @test
     */
    public function is_cacheable()
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
        $this->assertEquals($str, (string) new EnvironmentVariableDefinition('bar'));
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
        $this->assertEquals($str, (string) new EnvironmentVariableDefinition('bar', true, '<default>'));
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
        $this->assertEquals($str, (string) new EnvironmentVariableDefinition('bar', true, \DI\get('foo')));
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
        $this->assertEquals($str, (string) new EnvironmentVariableDefinition('bar', true, \DI\env('foo')));
    }
}
