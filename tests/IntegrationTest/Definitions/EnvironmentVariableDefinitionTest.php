<?php

declare(strict_types=1);

namespace DI\Test\IntegrationTest\Definitions;

use DI\ContainerBuilder;
use DI\Test\IntegrationTest\BaseContainerTest;

/**
 * Test environment variable definitions.
 */
class EnvironmentVariableDefinitionTest extends BaseContainerTest
{
    /**
     * @dataProvider provideContainer
     */
    public function test_existing_env_variable(ContainerBuilder $builder)
    {
        $expectedValue = getenv('USER');
        if (! $expectedValue) {
            $this->markTestSkipped(
                'This test relies on the presence of the USER environment variable.'
            );
        }

        $builder->addDefinitions([
            'var' => \DI\env('USER'),
        ]);
        $container = $builder->build();

        self::assertEntryIsCompiled($container, 'var');
        self::assertEquals($expectedValue, $container->get('var'));
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_nonexistent_env_variable(ContainerBuilder $builder)
    {
        $this->expectException('DI\Definition\Exception\InvalidDefinition');
        $this->expectExceptionMessage('The environment variable \'PHP_DI_DO_NOT_DEFINE_THIS\' has not been defined');
        $builder->addDefinitions([
            'var' => \DI\env('PHP_DI_DO_NOT_DEFINE_THIS'),
        ]);
        $container = $builder->build();

        $container->get('var');
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_nonexistent_env_variable_with_default_value(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            'var' => \DI\env('PHP_DI_DO_NOT_DEFINE_THIS', '<default>'),
        ]);
        $container = $builder->build();

        self::assertEntryIsCompiled($container, 'var');
        $this->assertEquals('<default>', $container->get('var'));
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_nonexistent_env_variable_with_null_as_default(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            'var' => \DI\env('PHP_DI_DO_NOT_DEFINE_THIS', null),
        ]);
        $container = $builder->build();

        self::assertEntryIsCompiled($container, 'var');
        $this->assertNull($container->get('var'));
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_nonexistent_env_variable_with_other_entry_as_default(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            'var' => \DI\env('PHP_DI_DO_NOT_DEFINE_THIS', \DI\get('foo')),
            'foo' => 'bar',
        ]);
        $container = $builder->build();

        self::assertEntryIsCompiled($container, 'var');
        $this->assertEquals('bar', $container->get('var'));
    }
}
