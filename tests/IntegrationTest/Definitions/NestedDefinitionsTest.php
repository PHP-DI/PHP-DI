<?php

namespace DI\Test\IntegrationTest\Definitions;

use DI\ContainerBuilder;
use DI\Test\IntegrationTest\Fixtures\Class1;
use DI\Test\IntegrationTest\Fixtures\Class2;
use DI\Test\IntegrationTest\Fixtures\Implementation1;
use DI\Test\IntegrationTest\Fixtures\LazyDependency;

class NestedDefinitionsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function should_allow_nested_definitions_in_environment_variables()
    {
        $builder = new ContainerBuilder();

        $builder->addDefinitions([
            'foo'    => 'bar',
            'link'   => \DI\env('PHP_DI_DO_NOT_DEFINE_THIS', \DI\get('foo')),
            'object' => \DI\env('PHP_DI_DO_NOT_DEFINE_THIS', \DI\create('stdClass')),
        ]);

        $container = $builder->build();

        $this->assertEquals('bar', $container->get('link'));
        $this->assertEquals(new \stdClass(), $container->get('object'));
    }

    /**
     * @test
     */
    public function should_allow_nested_definitions_in_object_definitions()
    {
        $builder = new ContainerBuilder();
        $builder->useAnnotations(false);

        $impl = new Implementation1();
        $lazyDep = new LazyDependency();

        $builder->addDefinitions([
            'foo' => 'bar',
            LazyDependency::class => $lazyDep,
            'obj' => \DI\create(Class1::class)
                ->constructor(
                    \DI\create(Class2::class),
                    \DI\factory(function () use ($impl) {
                        return $impl;
                    }),
                    \DI\get(LazyDependency::class)
                )
                ->property('property1', \DI\get('foo'))
                ->property('property2', \DI\factory(function () use ($impl) {
                    return $impl;
                })),
        ]);

        $container = $builder->build();
        /** @var Class1 $obj */
        $obj = $container->get('obj');

        // Assertions on constructor parameters
        $this->assertInstanceOf(Class2::class, $obj->constructorParam1);
        $this->assertSame($impl, $obj->constructorParam2);
        $this->assertSame($lazyDep, $obj->constructorParam3);

        // Assertions on properties
        $this->assertEquals('bar', $obj->property1);
        $this->assertSame($impl, $obj->property2);
    }

    /**
     * @test
     */
    public function should_allow_nested_definitions_in_arrays()
    {
        $builder = new ContainerBuilder();

        $builder->addDefinitions([
            'foo'   => 'bar',
            'array' => [
                'env'    => \DI\env('PHP_DI_DO_NOT_DEFINE_THIS', \DI\get('foo')),
                'link'   => \DI\get('foo'),
                'object' => \DI\create('stdClass'),
            ],
        ]);

        $container = $builder->build();

        $expected = [
            'env'    => 'bar',
            'link'   => 'bar',
            'object' => new \stdClass(),
        ];

        $this->assertEquals($expected, $container->get('array'));
    }
}
