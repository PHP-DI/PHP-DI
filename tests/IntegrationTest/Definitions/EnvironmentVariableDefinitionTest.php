<?php

namespace DI\Test\IntegrationTest\Definitions;

use DI\ContainerBuilder;

/**
 * Test environment variable definitions
 *
 * @coversNothing
 */
class EnvironmentVariableDefinitionTest extends \PHPUnit_Framework_TestCase
{
    public function test_existing_env_variable()
    {
        $expectedValue = getenv('USER');
        if (! $expectedValue) {
            $this->markTestSkipped(
                'This test relies on the presence of the USER environment variable.'
            );
        }

        $builder = new ContainerBuilder();
        $builder->addDefinitions([
            'var' => \DI\env('USER'),
        ]);
        $container = $builder->build();

        $this->assertEquals($expectedValue, $container->get('var'));
    }

    /**
     * @expectedException \DI\Definition\Exception\DefinitionException
     * @expectedExceptionMessage The environment variable 'PHP_DI_DO_NOT_DEFINE_THIS' has not been defined
     */
    public function test_nonexistent_env_variable()
    {
        $builder = new ContainerBuilder();
        $builder->addDefinitions([
            'var' => \DI\env('PHP_DI_DO_NOT_DEFINE_THIS'),
        ]);
        $container = $builder->build();

        $container->get('var');
    }

    public function test_nonexistent_env_variable_with_default_value()
    {
        $builder = new ContainerBuilder();
        $builder->addDefinitions([
            'var' => \DI\env('PHP_DI_DO_NOT_DEFINE_THIS', '<default>'),
        ]);
        $container = $builder->build();

        $this->assertEquals('<default>', $container->get('var'));
    }

    public function test_nonexistent_env_variable_with_null_as_default()
    {
        $builder = new ContainerBuilder();
        $builder->addDefinitions([
            'var' => \DI\env('PHP_DI_DO_NOT_DEFINE_THIS', null),
        ]);
        $container = $builder->build();

        $this->assertNull($container->get('var'));
    }

    public function test_nonexistent_env_variable_with_other_entry_as_default()
    {
        $builder = new ContainerBuilder();
        $builder->addDefinitions([
            'var' => \DI\env('PHP_DI_DO_NOT_DEFINE_THIS', \DI\get('foo')),
            'foo' => 'bar',
        ]);
        $container = $builder->build();

        $this->assertEquals('bar', $container->get('var'));
    }
}
