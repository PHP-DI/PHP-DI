<?php

namespace DI\Test\IntegrationTest\Definitions;

use DI\ContainerBuilder;

/**
 * Test object definitions.
 *
 * TODO add more tests
 *
 * @coversNothing
 */
class ObjectDefinitionTest extends \PHPUnit_Framework_TestCase
{
    public function test_object_without_autowiring()
    {
        $builder = new ContainerBuilder();
        $builder->useAutowiring(false);
        $builder->addDefinitions([
            // with the same name
            'stdClass' => \DI\object('stdClass'),
            // with name inferred
            __NAMESPACE__ . '\ObjectDefinition\Class1' => \DI\object(),
            // with a different name
            'object' => \DI\object(__NAMESPACE__ . '\ObjectDefinition\Class1'),
        ]);
        $container = $builder->build();

        $this->assertInstanceOf('stdClass', $container->get('stdClass'));
        $this->assertInstanceOf(__NAMESPACE__ . '\ObjectDefinition\Class1', $container->get(__NAMESPACE__ . '\ObjectDefinition\Class1'));
        $this->assertInstanceOf(__NAMESPACE__ . '\ObjectDefinition\Class1', $container->get('object'));
    }

    public function test_multiple_method_call()
    {
        $builder = new ContainerBuilder();
        $builder->useAutowiring(false);
        $builder->addDefinitions([
            __NAMESPACE__ . '\ObjectDefinition\Class1' => \DI\object()
                ->method('increment')
                ->method('increment'),
        ]);
        $container = $builder->build();

        $class = $container->get(__NAMESPACE__ . '\ObjectDefinition\Class1');
        $this->assertEquals(2, $class->count);
    }

    public function test_override_parameter_with_multiple_method_call()
    {
        $builder = new ContainerBuilder();
        $builder->useAutowiring(false);
        $builder->addDefinitions([
            __NAMESPACE__ . '\ObjectDefinition\Class1' => \DI\object()
                ->method('add', 'foo')
                ->method('add', 'foo'),
        ]);
        $builder->addDefinitions([
            // Override a method parameter
            __NAMESPACE__ . '\ObjectDefinition\Class1' => \DI\object()
                ->methodParameter('add', 0, 'bar'),
        ]);
        $container = $builder->build();

        // Should override only the first method call
        $class = $container->get(__NAMESPACE__ . '\ObjectDefinition\Class1');
        $this->assertEquals(['bar', 'foo'], $class->items);
    }
}
