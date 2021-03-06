<?php

declare(strict_types=1);

namespace DI\Test\IntegrationTest\Annotations;

use DI\ContainerBuilder;
use DI\Test\IntegrationTest\Annotations\InjectWithUseStatements\InjectWithUseStatements2;
use DI\Test\IntegrationTest\BaseContainerTest;

/**
 * Test using annotations.
 */
class AnnotationsTest extends BaseContainerTest
{
    /**
     * @test
     * @dataProvider provideContainer
     */
    public function inject_in_properties(ContainerBuilder $builder)
    {
        $builder->useAnnotations(true);

        /** @var B $object */
        $object = $builder->build()->get(B::class);

        $this->assertInstanceOf(A::class, $object->public);
        $this->assertInstanceOf(A::class, $object->getProtected());
        $this->assertInstanceOf(A::class, $object->getPrivate());
    }

    /**
     * Inject in parent properties (public, protected and private).
     *
     * @test
     * @dataProvider provideContainer
     */
    public function inject_in_parent_properties(ContainerBuilder $builder)
    {
        $builder->useAnnotations(true);
        $container = $builder->build();

        /** @var C $object */
        $object = $container->get(C::class);
        $this->assertInstanceOf(A::class, $object->public);
        $this->assertInstanceOf(A::class, $object->getProtected());
        $this->assertInstanceOf(A::class, $object->getPrivate());

        /** @var D $object */
        $object = $container->get(D::class);
        $this->assertInstanceOf(A::class, $object->public);
        $this->assertInstanceOf(A::class, $object->getProtected());
        $this->assertInstanceOf(A::class, $object->getPrivate());
    }

    /**
     * Inject in private parent properties even if they have the same name of child properties.
     *
     * @test
     * @dataProvider provideContainer
     */
    public function inject_in_private_parent_properties_with_same_name(ContainerBuilder $builder)
    {
        $builder->useAnnotations(true);
        $container = $builder->build();

        /** @var Child $object */
        $object = $container->get(Child::class);
        $this->assertInstanceOf(A::class, $object->public);
        $this->assertInstanceOf(A::class, $object->getProtected());
        $this->assertInstanceOf(A::class, $object->getPrivate());
        $this->assertInstanceOf(A::class, $object->getChildPrivate());
    }

    /**
     * @test
     * @dataProvider provideContainer
     */
    public function inject_by_name(ContainerBuilder $builder)
    {
        $builder->useAnnotations(true);

        $dependency = new \stdClass();

        $builder->addDefinitions([
            'namedDependency'  => $dependency,
        ]);
        $container = $builder->build();

        /** @var NamedInjection $object */
        $object = $container->get(NamedInjection::class);
        $this->assertSame($dependency, $object->dependency);
    }

    /**
     * @test
     * @dataProvider provideContainer
     */
    public function errors_if_dependency_by_name_not_found(ContainerBuilder $builder)
    {
        $this->expectException('DI\DependencyException');
        $builder->useAnnotations(true);
        $builder->build()->get(NamedInjection::class);
    }
}
