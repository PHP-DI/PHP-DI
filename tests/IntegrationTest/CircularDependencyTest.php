<?php

namespace DI\Test\UnitTest;

use DI\ContainerBuilder;
use function DI\create;
use function DI\get;
use DI\Test\IntegrationTest\BaseContainerTest;
use DI\Test\UnitTest\Fixtures\Class1CircularDependencies;
use DI\Test\UnitTest\Fixtures\Class2CircularDependencies;

/**
 * Test that circular dependencies are handled correctly.
 */
class CircularDependencyTest extends BaseContainerTest
{
    /**
     * Tests if instantiation unlock works: we should be able to get the same entry twice.
     * @test
     * @dataProvider provideContainer
     */
    public function can_get_the_same_entry_twice(ContainerBuilder $builder)
    {
        $container = $builder->build();
        $container->get(\stdClass::class);
        $container->get(\stdClass::class);
    }

    /**
     * @test
     * @dataProvider provideContainer
     * @expectedException \DI\DependencyException
     * @expectedExceptionMessage Circular dependency detected while trying to resolve entry 'DI\Test\UnitTest\Fixtures\Class1CircularDependencies'
     */
    public function circular_dependencies_throw_exceptions(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            Class1CircularDependencies::class => create()
                ->property('class2', get(Class2CircularDependencies::class)),
            Class2CircularDependencies::class => create()
                ->property('class1', get(Class1CircularDependencies::class)),
        ]);
        $builder->build()->get(Class1CircularDependencies::class);
    }

    /**
     * @test
     * @dataProvider provideContainer
     * @expectedException \DI\DependencyException
     * @expectedExceptionMessage Circular dependency detected while trying to resolve entry 'DI\Test\UnitTest\Fixtures\Class1CircularDependencies'
     */
    public function circular_dependencies_with_annotations_throw_exceptions(ContainerBuilder $builder)
    {
        $builder->useAnnotations(true);
        $builder->build()->get(Class1CircularDependencies::class);
    }

    /**
     * @test
     * @dataProvider provideContainer
     * @expectedException \DI\DependencyException
     * @expectedExceptionMessage Circular dependency detected while trying to resolve entry 'foo'
     */
    public function circular_dependencies_because_of_self_alias_throw_exceptions(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            // Alias to itself -> infinite recursive loop
            'foo' => get('foo'),
        ]);
        $builder->build()->get('foo');
    }
}
