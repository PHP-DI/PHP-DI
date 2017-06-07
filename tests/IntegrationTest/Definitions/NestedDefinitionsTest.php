<?php

declare(strict_types=1);

namespace DI\Test\IntegrationTest\Definitions;

use DI\ContainerBuilder;
use DI\Test\IntegrationTest\BaseContainerTest;
use DI\Test\IntegrationTest\Definitions\NestedDefinitionsTest\AllKindsOfInjections;
use function DI\autowire;
use function DI\create;
use function DI\env;
use function DI\get;

class NestedDefinitionsTest extends BaseContainerTest
{
    /**
     * @test
     * @dataProvider provideContainer
     */
    public function should_allow_nested_definitions_in_environment_variables(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            'foo' => 'bar',
            'link' => \DI\env('PHP_DI_DO_NOT_DEFINE_THIS', \DI\get('foo')),
            'object' => \DI\env('PHP_DI_DO_NOT_DEFINE_THIS', \DI\create('stdClass')),
            'objectInArray' => \DI\env('PHP_DI_DO_NOT_DEFINE_THIS', [\DI\create('stdClass')]),
            'factory' => \DI\env('PHP_DI_DO_NOT_DEFINE_THIS', \DI\factory(function () {
                return 'hello';
            })),
        ]);
        $container = $builder->build();

        $this->assertEquals('bar', $container->get('link'));
        $this->assertEquals(new \stdClass, $container->get('object'));
        $this->assertEquals([new \stdClass], $container->get('objectInArray'));
        $this->assertEquals('hello', $container->get('factory'));
    }

    /**
     * @test
     * @dataProvider provideContainer
     */
    public function should_allow_nested_definitions_in_factories(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            'factory' => \DI\factory(function ($entry) {
                return $entry;
            })->parameter('entry', [create(\stdClass::class)]),
        ]);

        $factory = $builder->build()->get('factory');

        $this->assertInstanceOf(\stdClass::class, $factory[0]);
    }

    /**
     * @test
     * @dataProvider provideContainer
     */
    public function should_allow_nested_definitions_in_create_definitions(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            AllKindsOfInjections::class => create()
                ->constructor(create('stdClass'))
                ->property('property', create('stdClass'))
                ->method('method', \DI\factory(function () {
                    return new \stdClass;
                })),
        ]);
        $container = $builder->build();

        $object = $container->get(AllKindsOfInjections::class);

        $this->assertEquals(new \stdClass, $object->property);
        $this->assertEquals(new \stdClass, $object->constructorParameter);
        $this->assertEquals(new \stdClass, $object->methodParameter);
    }

    /**
     * @test
     * @dataProvider provideContainer
     */
    public function should_allow_nested_definitions_in_arrays_in_create_definitions(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            AllKindsOfInjections::class => create()
                ->constructor([
                    create('stdClass'),
                ])
                ->property('property', [
                    create('stdClass'),
                ])
                ->method('method', [
                    \DI\factory(function () {
                        return new \stdClass;
                    }),
                ]),
        ]);
        $container = $builder->build();

        $object = $container->get(AllKindsOfInjections::class);

        $this->assertEquals(new \stdClass, $object->property[0]);
        $this->assertEquals(new \stdClass, $object->constructorParameter[0]);
        $this->assertEquals(new \stdClass, $object->methodParameter[0]);
    }

    /**
     * @test
     * @dataProvider provideContainer
     */
    public function should_allow_nested_definitions_in_autowire_definitions(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            AllKindsOfInjections::class => autowire()
                ->constructorParameter('constructorParameter', create('stdClass'))
                ->property('property', create('stdClass'))
                ->methodParameter('method', 'methodParameter', \DI\factory(function () {
                    return new \stdClass;
                })),
        ]);
        $container = $builder->build();

        $object = $container->get(AllKindsOfInjections::class);

        $this->assertEquals(new \stdClass, $object->property);
        $this->assertEquals(new \stdClass, $object->constructorParameter);
        $this->assertEquals(new \stdClass, $object->methodParameter);
    }

    /**
     * @test
     * @dataProvider provideContainer
     */
    public function should_allow_nested_definitions_in_arrays_in_autowire_definitions(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            AllKindsOfInjections::class => autowire()
                ->constructorParameter('constructorParameter', [
                    create('stdClass'),
                ])
                ->property('property', [
                    create('stdClass'),
                ])
                ->methodParameter('method', 'methodParameter', [
                    \DI\factory(function () {
                        return new \stdClass;
                    }),
                ]),
        ]);
        $container = $builder->build();

        $object = $container->get(AllKindsOfInjections::class);

        $this->assertEquals(new \stdClass, $object->property[0]);
        $this->assertEquals(new \stdClass, $object->constructorParameter[0]);
        $this->assertEquals(new \stdClass, $object->methodParameter[0]);
    }

    /**
     * @test
     * @dataProvider provideContainer
     */
    public function should_allow_nested_definitions_in_arrays(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            'foo'   => 'bar',
            'array' => [
                'env'    => env('PHP_DI_DO_NOT_DEFINE_THIS', get('foo')),
                'link'   => get('foo'),
                'object' => create('stdClass'),
                'objectInArray' => [create('stdClass')],
                'autowired' => autowire('stdClass'),
                'array' => [
                    'object' => create('stdClass'),
                ],
                'factory' => \DI\factory(function () {
                    return 'hello';
                })
            ],
        ]);

        $container = $builder->build();

        $expected = [
            'env'    => 'bar',
            'link'   => 'bar',
            'object' => new \stdClass,
            'objectInArray' => [new \stdClass],
            'autowired' => new \stdClass,
            'array' => [
                'object' => new \stdClass,
            ],
            'factory' => 'hello',
        ];

        $this->assertEquals($expected, $container->get('array'));
    }
}

namespace DI\Test\IntegrationTest\Definitions\NestedDefinitionsTest;

class AllKindsOfInjections
{
    public $property;
    public $constructorParameter;
    public $methodParameter;

    public function __construct($constructorParameter)
    {
        $this->constructorParameter = $constructorParameter;
    }

    public function method($methodParameter)
    {
        $this->methodParameter = $methodParameter;
    }
}
