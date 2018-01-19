<?php

declare(strict_types=1);

namespace DI\Test\IntegrationTest\Definitions;

use DI\ContainerBuilder;
use DI\Test\IntegrationTest\BaseContainerTest;

/**
 * Test array definitions.
 */
class ArrayDefinitionTest extends BaseContainerTest
{
    /**
     * @dataProvider provideContainer
     */
    public function test_array_with_values(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            'values' => [
                'value 1',
                'value 2',
            ],
        ]);
        $container = $builder->build();

        $array = $container->get('values');

        $this->assertEntryIsCompiled($container, 'values');
        $this->assertEquals('value 1', $array[0]);
        $this->assertEquals('value 2', $array[1]);
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_array_containing_sub_array(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            'values' => [
                [
                    'value 1',
                    'value 2',
                ],
                [
                    'value 1',
                    'value 2',
                ],
            ],
        ]);
        $container = $builder->build();

        $array = $container->get('values');

        $this->assertEntryIsCompiled($container, 'values');
        $this->assertEquals('value 1', $array[0][0]);
        $this->assertEquals('value 2', $array[0][1]);
        $this->assertEquals('value 1', $array[1][0]);
        $this->assertEquals('value 2', $array[1][1]);
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_array_with_links(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            'links'     => [
                \DI\get('dependency1'),
                \DI\get('dependency2'),
            ],
            'dependency1' => \DI\create('stdClass'),
            'dependency2' => \DI\create('stdClass'),
        ]);
        $container = $builder->build();

        $array = $container->get('links');

        $this->assertEntryIsCompiled($container, 'links');
        $this->assertInstanceOf(\stdClass::class, $array[0]);
        $this->assertInstanceOf(\stdClass::class, $array[1]);
        $this->assertSame($container->get('dependency1'), $array[0]);
        $this->assertSame($container->get('dependency2'), $array[1]);
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_array_with_nested_definitions(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            'array' => [
                \DI\env('PHP_DI_DO_NOT_DEFINE_THIS', 'env'),
                \DI\create('stdClass'),
            ],
        ]);
        $container = $builder->build();

        $array = $container->get('array');

        $this->assertEntryIsCompiled($container, 'array');
        $this->assertEquals('env', $array[0]);
        $this->assertEquals(new \stdClass, $array[1]);
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_nested_array_with_nested_definitions(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            'array' => [
                'array' => [
                    \DI\env('PHP_DI_DO_NOT_DEFINE_THIS', 'env'),
                    \DI\create('stdClass'),
                ],
            ],
        ]);
        $container = $builder->build();

        $array = $container->get('array');

        $this->assertEntryIsCompiled($container, 'array');
        $this->assertEquals('env', $array['array'][0]);
        $this->assertEquals(new \stdClass, $array['array'][1]);
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_nested_array_preserve_keys(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            'array' => [
                'array' => [
                    'foo',
                    'bar',
                ],
            ],
        ]);
        $container = $builder->build();

        $array = $container->get('array');

        $this->assertEntryIsCompiled($container, 'array');
        $this->assertEquals('foo', $array['array'][0]);
        $this->assertEquals('bar', $array['array'][1]);
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_add_entries(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            'values' => [
                'value 1',
                'value 2',
            ],
        ]);
        $builder->addDefinitions([
            'values' => \DI\add([
                'another value',
                \DI\get('foo'),
            ]),
            'foo'    => \DI\create('stdClass'),
        ]);
        $container = $builder->build();

        $array = $container->get('values');

        $this->assertEntryIsCompiled($container, 'values');
        $this->assertCount(4, $array);
        $this->assertEquals('value 1', $array[0]);
        $this->assertEquals('value 2', $array[1]);
        $this->assertEquals('another value', $array[2]);
        $this->assertInstanceOf(\stdClass::class, $array[3]);
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_add_entries_with_nested_definitions(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            'array' => [
                \DI\env('PHP_DI_DO_NOT_DEFINE_THIS', 'env'),
                \DI\create('stdClass'),
            ],
        ]);
        $builder->addDefinitions([
            'array' => \DI\add([
                \DI\env('PHP_DI_DO_NOT_DEFINE_THIS', 'foo'),
                \DI\create('stdClass'),
            ]),
        ]);
        $container = $builder->build();

        $array = $container->get('array');

        $this->assertEntryIsCompiled($container, 'array');
        $this->assertEquals('env', $array[0]);
        $this->assertEquals(new \stdClass, $array[1]);
        $this->assertEquals('foo', $array[2]);
        $this->assertEquals(new \stdClass, $array[3]);
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_add_to_non_existing_array_works(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            'values' => \DI\add([
                'value 1',
            ]),
        ]);
        $container = $builder->build();

        $array = $container->get('values');

        $this->assertEntryIsCompiled($container, 'values');
        $this->assertCount(1, $array);
        $this->assertEquals('value 1', $array[0]);
    }
}

namespace DI\Test\IntegrationTest\Definitions\ArrayDefinitionTest;

class SimpleClass
{
    public $dependency;
}
