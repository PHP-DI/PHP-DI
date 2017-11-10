<?php

declare(strict_types=1);

namespace DI\Test\IntegrationTest\Definitions;

use DI\ContainerBuilder;
use DI\Test\IntegrationTest\BaseContainerTest;
use DI\Test\IntegrationTest\Definitions\NestedDefinitionsTest\AllKindsOfInjections;
use DI\Test\IntegrationTest\Definitions\NestedDefinitionsTest\Autowireable;
use DI\Test\IntegrationTest\Definitions\NestedDefinitionsTest\AutowireableDependency;
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
            'autowired' => \DI\env('PHP_DI_DO_NOT_DEFINE_THIS', autowire(Autowireable::class)),
            'factory' => \DI\env('PHP_DI_DO_NOT_DEFINE_THIS', \DI\factory(function () {
                return 'hello';
            })),
        ]);
        $container = $builder->build();

        $this->assertEquals('bar', $container->get('link'));
        $this->assertEquals(new \stdClass, $container->get('object'));
        $this->assertEquals([new \stdClass], $container->get('objectInArray'));
        $this->assertEquals(new Autowireable(new AutowireableDependency), $container->get('autowired'));
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
            })->parameter('entry', [create(\stdClass::class), autowire(Autowireable::class)]),
        ]);

        $factory = $builder->build()->get('factory');

        $this->assertEquals(new \stdClass, $factory[0]);
        $this->assertEquals(new Autowireable(new AutowireableDependency), $factory[1]);
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
                ->property('property', autowire(Autowireable::class))
                ->method('method', \DI\factory(function () {
                    return new \stdClass;
                })),
        ]);
        $container = $builder->build();

        $object = $container->get(AllKindsOfInjections::class);

        $this->assertEquals(new Autowireable(new AutowireableDependency), $object->property);
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
                    autowire(Autowireable::class),
                ])
                ->method('method', [
                    \DI\factory(function () {
                        return new \stdClass;
                    }),
                ]),
        ]);
        $container = $builder->build();

        $object = $container->get(AllKindsOfInjections::class);

        $this->assertEquals(new Autowireable(new AutowireableDependency), $object->property[0]);
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
                ->property('property', autowire(Autowireable::class))
                ->methodParameter('method', 'methodParameter', \DI\factory(function () {
                    return new \stdClass;
                })),
        ]);
        $container = $builder->build();

        $object = $container->get(AllKindsOfInjections::class);

        $this->assertEquals(new Autowireable(new AutowireableDependency), $object->property);
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
                    autowire(Autowireable::class),
                ])
                ->methodParameter('method', 'methodParameter', [
                    \DI\factory(function () {
                        return new \stdClass;
                    }),
                ]),
        ]);
        $container = $builder->build();

        $object = $container->get(AllKindsOfInjections::class);

        $this->assertEquals(new Autowireable(new AutowireableDependency), $object->property[0]);
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
                'autowired' => autowire(Autowireable::class),
                'array' => [
                    'object' => create('stdClass'),
                ],
                'factory' => \DI\factory(function () {
                    return 'hello';
                }),
            ],
        ]);

        $container = $builder->build();

        $expected = [
            'env'    => 'bar',
            'link'   => 'bar',
            'object' => new \stdClass,
            'objectInArray' => [new \stdClass],
            'autowired' => new Autowireable(new AutowireableDependency),
            'array' => [
                'object' => new \stdClass,
            ],
            'factory' => 'hello',
        ];

        $this->assertEquals($expected, $container->get('array'));
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_anonymous_functions_can_be_nested_in_other_definitions(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            'array' => [
                function () { return 'hello'; },
            ],
            'env' => \DI\env('PHP_DI_DO_NOT_DEFINE_THIS', function () {
                return 'hello';
            }),
            'factory' => \DI\factory(function ($entry) {
                return $entry;
            })->parameter('entry', function () { return 'hello'; }),
            'object' => create(AllKindsOfInjections::class)
                ->constructor(function () { return 'hello'; })
                ->property('property', function () { return 'hello'; })
                ->method('method', function () { return 'hello'; }),
        ]);

        $container = $builder->build();

        self::assertEquals('hello', $container->get('array')[0]);
        self::assertEquals('hello', $container->get('env'));
        self::assertEquals('hello', $container->get('factory'));
        self::assertEquals('hello', $container->get('object')->property);
        self::assertEquals('hello', $container->get('object')->constructorParameter);
        self::assertEquals('hello', $container->get('object')->methodParameter);
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

class Autowireable
{
    private $dependency;

    public function __construct(AutowireableDependency $dependency)
    {
        $this->dependency = $dependency;
    }
}

class AutowireableDependency
{
}
