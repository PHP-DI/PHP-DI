<?php

declare(strict_types=1);

namespace DI\Test\UnitTest\Definition;

use DI\Definition\EnvironmentVariableDefinition;
use PHPUnit\Framework\TestCase;

/**
 * @covers \DI\Definition\EnvironmentVariableDefinition
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\DI\Definition\EnvironmentVariableDefinition::class)]
class EnvironmentVariableDefinitionTest extends TestCase
{
    public function test_getters()
    {
        $definition = new EnvironmentVariableDefinition('bar', false, 'default');
        $definition->setName('foo');

        $this->assertEquals('foo', $definition->getName());
        $this->assertEquals('bar', $definition->getVariableName());
        $this->assertFalse($definition->isOptional());
        $this->assertEquals('default', $definition->getDefaultValue());
    }

    /**
     * @test
     */
    #[\PHPUnit\Framework\Attributes\Test]
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
    #[\PHPUnit\Framework\Attributes\Test]
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
    #[\PHPUnit\Framework\Attributes\Test]
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
    #[\PHPUnit\Framework\Attributes\Test]
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
        $this->assertEquals($str, (string) new EnvironmentVariableDefinition('bar', true, new EnvironmentVariableDefinition('foo')));
    }
}
