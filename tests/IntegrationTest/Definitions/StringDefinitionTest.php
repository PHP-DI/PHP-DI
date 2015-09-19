<?php

namespace DI\Test\IntegrationTest\Definitions;

use DI\ContainerBuilder;

/**
 * Test string definitions
 *
 * @coversNothing
 */
class StringDefinitionTest extends \PHPUnit_Framework_TestCase
{
    public function test_string_without_placeholder()
    {
        $builder = new ContainerBuilder();
        $builder->addDefinitions([
            'foo' => \DI\string('bar'),
        ]);
        $container = $builder->build();

        $this->assertEquals('bar', $container->get('foo'));
    }

    public function test_string_with_placeholder()
    {
        $builder = new ContainerBuilder();
        $builder->addDefinitions([
            'foo'         => 'bar',
            'test-string' => \DI\string('Hello {foo}'),
        ]);
        $container = $builder->build();

        $this->assertEquals('Hello bar', $container->get('test-string'));
    }

    public function test_string_with_multiple_placeholders()
    {
        $builder = new ContainerBuilder();
        $builder->addDefinitions([
            'foo'         => 'bar',
            'bim'         => 'bam',
            'test-string' => \DI\string('Hello {foo}, {bim}'),
        ]);
        $container = $builder->build();

        $this->assertEquals('Hello bar, bam', $container->get('test-string'));
    }

    public function test_nested_string_expressions()
    {
        $builder = new ContainerBuilder();
        $builder->addDefinitions([
            'name'        => 'John',
            'welcome'     => \DI\string('Welcome {name}'),
            'test-string' => \DI\string('{welcome}!'),
        ]);
        $container = $builder->build();

        $this->assertEquals('Welcome John!', $container->get('test-string'));
    }

    /**
     * @expectedException \DI\DependencyException
     * @expectedExceptionMessage Error while parsing string expression for entry 'test-string': No entry or class found
     *                           for 'foo'
     */
    public function test_string_with_nonexistent_placeholder()
    {
        $builder = new ContainerBuilder();
        $builder->addDefinitions([
            'test-string' => \DI\string('Hello {foo}'),
        ]);
        $container = $builder->build();

        $container->get('test-string');
    }
}
