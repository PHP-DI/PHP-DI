<?php

namespace DI\Test\IntegrationTest\Definitions;

use DI\ContainerBuilder;
use DI\Test\IntegrationTest\Definitions\ObjectDefinition\Class1;

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
            Class1::class => \DI\object(),
            // with a different name
            'object' => \DI\object(Class1::class),
        ]);
        $container = $builder->build();

        $this->assertInstanceOf('stdClass', $container->get('stdClass'));
        $this->assertInstanceOf(Class1::class, $container->get(Class1::class));
        $this->assertInstanceOf(Class1::class, $container->get('object'));
    }

    public function test_multiple_method_call()
    {
        $builder = new ContainerBuilder();
        $builder->useAutowiring(false);
        $builder->addDefinitions([
            Class1::class => \DI\object()
                ->method('increment')
                ->method('increment'),
        ]);
        $container = $builder->build();

        $class = $container->get(Class1::class);
        $this->assertEquals(2, $class->count);
    }

    public function test_override_parameter_with_multiple_method_call()
    {
        $builder = new ContainerBuilder();
        $builder->useAutowiring(false);
        $builder->addDefinitions([
            Class1::class => \DI\object()
                ->method('add', 'foo')
                ->method('add', 'foo'),
        ]);
        $builder->addDefinitions([
            // Override a method parameter
            Class1::class => \DI\object()
                ->methodParameter('add', 0, 'bar'),
        ]);
        $container = $builder->build();

        // Should override only the first method call
        $class = $container->get(Class1::class);
        $this->assertEquals(['bar', 'foo'], $class->items);
    }
}
