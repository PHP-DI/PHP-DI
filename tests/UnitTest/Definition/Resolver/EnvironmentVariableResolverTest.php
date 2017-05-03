<?php

namespace DI\Test\UnitTest\Definition\Resolver;

use DI\Definition\AliasDefinition;
use DI\Definition\EnvironmentVariableDefinition;
use DI\Definition\Resolver\DefinitionResolver;
use DI\Definition\Resolver\EnvironmentVariableResolver;
use EasyMock\EasyMock;
use PHPUnit_Framework_MockObject_MockObject;

/**
 * @covers \DI\Definition\Resolver\EnvironmentVariableResolver
 */
class EnvironmentVariableResolverTest extends \PHPUnit_Framework_TestCase
{
    use EasyMock;

    /**
     * @var EnvironmentVariableResolver
     */
    private $resolver;
    /**
     * @var DefinitionResolver|PHPUnit_Framework_MockObject_MockObject
     */
    private $parentResolver;

    private $definedDefinition;
    private $undefinedDefinition;
    private $optionalDefinition;
    private $nestedDefinition;

    public function setUp()
    {
        $this->parentResolver = $this->easyMock(DefinitionResolver::class);

        $variableReader = function ($variableName) {
            if ('DEFINED' === $variableName) {
                return '<value>';
            }

            return false;
        };

        $this->resolver = new EnvironmentVariableResolver($this->parentResolver, $variableReader);
        $this->definedDefinition = new EnvironmentVariableDefinition('foo', 'DEFINED');
        $this->undefinedDefinition = new EnvironmentVariableDefinition('foo', 'UNDEFINED');
        $this->optionalDefinition = new EnvironmentVariableDefinition('foo', 'UNDEFINED', true, '<default>');
        $this->nestedDefinition = new EnvironmentVariableDefinition('foo', 'UNDEFINED', true, \DI\get('foo'));
    }

    /**
     * @test
     */
    public function should_resolve_existing_env_variable()
    {
        $value = $this->resolver->resolve($this->definedDefinition);

        $this->assertEquals('<value>', $value);
    }

    /**
     * @test
     */
    public function should_return_default_value_when_env_variable_is_undefined()
    {
        $value = $this->resolver->resolve($this->optionalDefinition);

        $this->assertEquals('<default>', $value);
    }

    /**
     * @test
     */
    public function should_resolve_nested_definition_in_default_value()
    {
        $this->parentResolver->expects($this->once())
            ->method('resolve')
            ->with(new AliasDefinition('', 'foo'))
            ->will($this->returnValue('bar'));

        $value = $this->resolver->resolve($this->nestedDefinition);

        $this->assertEquals('bar', $value);
    }

    /**
     * @test
     * @expectedException \DI\Definition\Exception\InvalidDefinition
     * @expectedExceptionMessage The environment variable 'UNDEFINED' has not been defined
     */
    public function should_throw_if_undefined_env_variable_and_no_default()
    {
        $this->resolver->resolve($this->undefinedDefinition);
    }

    /**
     * @test
     */
    public function should_be_able_to_resolve_defined_env_variables()
    {
        $this->assertTrue($this->resolver->isResolvable($this->definedDefinition));
    }

    /**
     * @test
     *
     * @see https://github.com/container-interop/container-interop/issues/37
     */
    public function should_be_able_to_resolve_undefined_env_variables()
    {
        $this->assertTrue($this->resolver->isResolvable($this->undefinedDefinition));
    }

    /**
     * @test
     */
    public function should_be_able_to_resolve_undefined_env_variables_with_default_values()
    {
        $this->assertTrue($this->resolver->isResolvable($this->optionalDefinition));
    }
}
