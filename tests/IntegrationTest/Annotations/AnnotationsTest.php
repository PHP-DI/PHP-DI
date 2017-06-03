<?php

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

        $this->assertTrue($object->public instanceof A);
        $this->assertTrue($object->getProtected() instanceof A);
        $this->assertTrue($object->getPrivate() instanceof A);
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
        $this->assertTrue($object->public instanceof A);
        $this->assertTrue($object->getProtected() instanceof A);
        $this->assertTrue($object->getPrivate() instanceof A);

        /** @var D $object */
        $object = $container->get(D::class);
        $this->assertTrue($object->public instanceof A);
        $this->assertTrue($object->getProtected() instanceof A);
        $this->assertTrue($object->getPrivate() instanceof A);
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
        $this->assertTrue($object->public instanceof A);
        $this->assertTrue($object->getProtected() instanceof A);
        $this->assertTrue($object->getPrivate() instanceof A);
        $this->assertTrue($object->getChildPrivate() instanceof A);
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
     * @expectedException \DI\DependencyException
     */
    public function errors_if_dependency_by_name_not_found(ContainerBuilder $builder)
    {
        $builder->useAnnotations(true);
        $builder->build()->get(NamedInjection::class);
    }

    /**
     * Check that @ var annotation takes "use" statements into account.
     * @test
     * @dataProvider provideContainer
     * @link https://github.com/PHP-DI/PHP-DI/issues/1
     */
    public function resolve_class_names_using_import_statements(ContainerBuilder $builder)
    {
        $builder->useAnnotations(true);
        $container = $builder->build();

        /** @var $object InjectWithUseStatements */
        $object = $container->get(InjectWithUseStatements::class);
        $this->assertTrue($object->a instanceof A);
        $this->assertTrue($object->alias instanceof A);
        $this->assertTrue($object->namespaceAlias instanceof A);

        /** @var $object InjectWithUseStatements2 */
        $object = $container->get(InjectWithUseStatements2::class);
        $this->assertTrue($object->dependency instanceof InjectWithUseStatements);
    }

    /**
     * @test
     * @dataProvider provideContainer
     * @expectedException \PhpDocReader\AnnotationException
     */
    public function testNotFoundVarAnnotation(ContainerBuilder $builder)
    {
        $builder->useAnnotations(true);
        $builder->build()->get(NotFoundVarAnnotation::class);
    }
}
