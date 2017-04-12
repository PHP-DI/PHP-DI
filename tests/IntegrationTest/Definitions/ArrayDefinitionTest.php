<?php

namespace DI\Test\IntegrationTest\Definitions;

use DI\ContainerBuilder;
use DI\Scope;
use DI\Test\IntegrationTest\Definitions\ObjectDefinition\A;
use DI\Test\IntegrationTest\Definitions\ObjectDefinition\B;
use DI\Test\IntegrationTest\Definitions\ObjectDefinition\C;

/**
 * Test array definitions.
 *
 * @coversNothing
 */
class ArrayDefinitionTest extends \PHPUnit_Framework_TestCase
{
    public function test_array_with_values()
    {
        $builder = new ContainerBuilder();
        $builder->addDefinitions([
            'values' => [
                'value 1',
                'value 2',
            ],
        ]);
        $container = $builder->build();

        $array = $container->get('values');

        $this->assertEquals('value 1', $array[0]);
        $this->assertEquals('value 2', $array[1]);
    }

    public function test_array_with_links()
    {
        $builder = new ContainerBuilder();
        $builder->addDefinitions([
            'links'     => [
                \DI\get('singleton'),
                \DI\get('prototype'),
            ],
            'singleton' => \DI\object('stdClass'),
            'prototype' => \DI\object('stdClass')
                ->scope(Scope::PROTOTYPE),
        ]);
        $container = $builder->build();

        $array = $container->get('links');

        $this->assertTrue($array[0] instanceof \stdClass);
        $this->assertTrue($array[1] instanceof \stdClass);

        $singleton = $container->get('singleton');
        $prototype = $container->get('prototype');

        $this->assertSame($singleton, $array[0]);
        $this->assertNotSame($prototype, $array[1]);
    }

    public function test_array_with_nested_definitions()
    {
        $builder = new ContainerBuilder();
        $builder->addDefinitions([
            'array' => [
                \DI\env('PHP_DI_DO_NOT_DEFINE_THIS', 'env'),
                \DI\object('stdClass'),
            ],
        ]);
        $container = $builder->build();

        $array = $container->get('array');

        $this->assertEquals('env', $array[0]);
        $this->assertEquals(new \stdClass, $array[1]);
    }

    /**
     * An array entry is a singleton.
     */
    public function test_array_with_prototype_entries()
    {
        $builder = new ContainerBuilder();
        $builder->addDefinitions([
            'array'     => [
                \DI\get('prototype'),
            ],
            'prototype' => \DI\object('stdClass')
                ->scope(Scope::PROTOTYPE),
        ]);
        $container = $builder->build();

        $array1 = $container->get('array');
        $array2 = $container->get('array');

        $this->assertSame($array1[0], $array2[0]);
    }

    public function test_add_entries()
    {
        $builder = new ContainerBuilder();
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
            'foo'    => \DI\object('stdClass'),
        ]);
        $container = $builder->build();

        $array = $container->get('values');

        $this->assertCount(4, $array);
        $this->assertEquals('value 1', $array[0]);
        $this->assertEquals('value 2', $array[1]);
        $this->assertEquals('another value', $array[2]);
        $this->assertTrue($array[3] instanceof \stdClass);
    }

    public function test_add_entries_with_nested_definitions()
    {
        $builder = new ContainerBuilder();
        $builder->addDefinitions([
            'array' => [
                \DI\env('PHP_DI_DO_NOT_DEFINE_THIS', 'env'),
                \DI\object('stdClass'),
            ],
        ]);
        $builder->addDefinitions([
            'array' => \DI\add([
                \DI\env('PHP_DI_DO_NOT_DEFINE_THIS', 'foo'),
                \DI\object('stdClass'),
            ]),
        ]);
        $container = $builder->build();

        $array = $container->get('array');

        $this->assertEquals('env', $array[0]);
        $this->assertEquals(new \stdClass, $array[1]);
        $this->assertEquals('foo', $array[2]);
        $this->assertEquals(new \stdClass, $array[3]);
    }

    public function test_add_to_non_existing_array_works()
    {
        $builder = new ContainerBuilder();
        $builder->addDefinitions([
            'values' => \DI\add([
                'value 1',
            ]),
        ]);
        $container = $builder->build();

        $array = $container->get('values');

        $this->assertCount(1, $array);
        $this->assertEquals('value 1', $array[0]);
    }

    public function test_autowiring_inside_arrays()
    {
        $builder = new ContainerBuilder();
        $builder->addDefinitions([
            'values' => [
                \DI\object(A::class),
                \DI\object(C::class)
            ]
        ]);
        $container = $builder->build();

        $array = $container->get('values');

        $this->assertCount(2, $array);
        $this->assertInstanceOf(A::class, $array[0]);
        $this->assertInstanceOf(C::class, $array[1]);
        $this->assertInstanceOf(B::class, $array[1]->a->b);
    }
}

