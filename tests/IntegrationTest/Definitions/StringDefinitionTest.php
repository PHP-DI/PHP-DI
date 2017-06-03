<?php

namespace DI\Test\IntegrationTest\Definitions;

use DI\ContainerBuilder;
use DI\Test\IntegrationTest\BaseContainerTest;

/**
 * Test string definitions.
 */
class StringDefinitionTest extends BaseContainerTest
{
    /**
     * @dataProvider provideContainer
     */
    public function test_string_without_placeholder(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            'foo' => \DI\string('bar'),
        ]);
        $container = $builder->build();

        $this->assertEquals('bar', $container->get('foo'));
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_string_with_placeholder(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            'foo'         => 'bar',
            'test-string' => \DI\string('Hello {foo}'),
        ]);
        $container = $builder->build();

        $this->assertEquals('Hello bar', $container->get('test-string'));
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_string_with_multiple_placeholders(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            'foo'         => 'bar',
            'bim'         => 'bam',
            'test-string' => \DI\string('Hello {foo}, {bim}'),
        ]);
        $container = $builder->build();

        $this->assertEquals('Hello bar, bam', $container->get('test-string'));
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_nested_string_expressions(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            'name'        => 'John',
            'welcome'     => \DI\string('Welcome {name}'),
            'test-string' => \DI\string('{welcome}!'),
        ]);
        $container = $builder->build();

        $this->assertEquals('Welcome John!', $container->get('test-string'));
    }

    /**
     * @dataProvider provideContainer
     * @expectedException \DI\DependencyException
     * @expectedExceptionMessage Error while parsing string expression for entry 'test-string': No entry or class found for 'foo'
     */
    public function test_string_with_nonexistent_placeholder(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            'test-string' => \DI\string('Hello {foo}'),
        ]);
        $container = $builder->build();

        $container->get('test-string');
    }
}
