<?php

namespace DI\Test\IntegrationTest\Definitions;

use DI\ContainerBuilder;
use DI\Test\IntegrationTest\BaseContainerTest;
use DI\Test\IntegrationTest\Definitions\ObjectDefinition\Class1;
use DI\Test\IntegrationTest\Definitions\ObjectDefinition\Class2;
use DI\Test\IntegrationTest\Definitions\ObjectDefinition\Class3;
use function DI\create;

/**
 * Test object definitions.
 *
 * TODO add more tests
 *
 * @coversNothing
 */
class CreateDefinitionTest extends BaseContainerTest
{
    /**
     * @dataProvider provideContainer
     */
    public function test_create_simple_object(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            // with the same name
            'stdClass' => create('stdClass'),
            // with name inferred
            Class1::class => create(),
            // with a different name
            'object' => create(Class1::class),
        ]);
        $container = $builder->build();

        $this->assertInstanceOf('stdClass', $container->get('stdClass'));
        $this->assertInstanceOf(Class1::class, $container->get(Class1::class));
        $this->assertInstanceOf(Class1::class, $container->get('object'));
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_singleton(ContainerBuilder $builder)
    {
        $container = $builder->addDefinitions([
            'stdClass' => create(),
        ])->build();

        self::assertSame($container->get('stdClass'), $container->get('stdClass'));
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_overrides_the_previous_entry(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            'foo' => create(Class2::class)
                ->property('bar', 123),
        ]);
        $builder->addDefinitions([
            'foo' => create(Class2::class)
                ->property('bim', 456),
        ]);
        $container = $builder->build();

        $foo = $container->get('foo');
        self::assertEquals(null, $foo->bar, 'The "bar" property is not set');
        self::assertEquals(456, $foo->bim, 'The "bim" property is set');
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_has_entry(ContainerBuilder $builder)
    {
        $container = $builder->addDefinitions([
            Class1::class => create(),
        ])->build();
        self::assertTrue($container->has(Class1::class));
    }

    /**
     * It should not inherit the definition from autowiring.
     * @dataProvider provideContainer
     * @expectedException \DI\Definition\Exception\InvalidDefinition
     * @expectedExceptionMessage Parameter $parameter of __construct() has no value defined or guessable
     */
    public function test_does_not_trigger_autowiring(ContainerBuilder $builder)
    {
        $builder->useAutowiring(true);
        $builder->addDefinitions([
            Class3::class => create(),
        ]);
        $container = $builder->build();
        $container->get(Class3::class);
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_same_method_can_be_called_multiple_times(ContainerBuilder $builder)
    {
        $builder->useAutowiring(false);
        $builder->addDefinitions([
            Class1::class => create()
                ->method('increment')
                ->method('increment'),
        ]);
        $container = $builder->build();

        $class = $container->get(Class1::class);
        $this->assertEquals(2, $class->count);
    }
}
