<?php

namespace DI\Test\IntegrationTest\Definitions;

use DI\ContainerBuilder;
use DI\Test\IntegrationTest\Definitions\ObjectDefinition\Class1;
use DI\Test\IntegrationTest\Definitions\ObjectDefinition\Class2;

/**
 * Test object definitions.
 *
 * TODO add more tests
 *
 * @coversNothing
 */
class ObjectDefinitionTest extends \PHPUnit_Framework_TestCase
{
    public function test_create_simple_object()
    {
        $builder = new ContainerBuilder();
        $builder->addDefinitions([
            // with the same name
            'stdClass' => \DI\create('stdClass'),
            // with name inferred
            Class1::class => \DI\create(),
            // with a different name
            'object' => \DI\create(Class1::class),
        ]);
        $container = $builder->build();

        $this->assertInstanceOf('stdClass', $container->get('stdClass'));
        $this->assertInstanceOf(Class1::class, $container->get(Class1::class));
        $this->assertInstanceOf(Class1::class, $container->get('object'));
    }

    public function test_create_overrides_the_previous_entry()
    {
        $builder = new ContainerBuilder();
        $builder->addDefinitions([
            'foo' => \DI\create(Class2::class)
                ->property('bar', 123),
        ]);
        $builder->addDefinitions([
            'foo' => \DI\create(Class2::class)
                ->property('bim', 456),
        ]);
        $container = $builder->build();

        $foo = $container->get('foo');
        self::assertEquals(null, $foo->bar, 'The "bar" property is not set');
        self::assertEquals(456, $foo->bim, 'The "bim" property is set');
    }

    public function test_multiple_method_call()
    {
        $builder = new ContainerBuilder();
        $builder->useAutowiring(false);
        $builder->addDefinitions([
            Class1::class => \DI\create()
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
