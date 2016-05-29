<?php

namespace DI\Test\IntegrationTest\Annotations;

use DI\ContainerBuilder;
use DI\Test\IntegrationTest\Annotations\InjectWithUseStatements\InjectWithUseStatements2;

/**
 * Test using annotations.
 *
 * @coversNothing
 */
class AnnotationsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function inject_in_properties()
    {
        $container = $this->createContainer();
        /** @var B $object */
        $object = $container->get(B::class);
        $this->assertTrue($object->public instanceof A);
        $this->assertTrue($object->getProtected() instanceof A);
        $this->assertTrue($object->getPrivate() instanceof A);
    }

    /**
     * Inject in parent properties (public, protected and private).
     *
     * @test
     */
    public function inject_in_parent_properties()
    {
        $container = $this->createContainer();

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
     */
    public function inject_in_private_parent_properties_with_same_name()
    {
        $container = $this->createContainer();

        /** @var Child $object */
        $object = $container->get(Child::class);
        $this->assertTrue($object->public instanceof A);
        $this->assertTrue($object->getProtected() instanceof A);
        $this->assertTrue($object->getPrivate() instanceof A);
        $this->assertTrue($object->getChildPrivate() instanceof A);
    }

    /**
     * @test
     */
    public function inject_by_name()
    {
        $dependency = new \stdClass();

        $container = $this->createContainer([
            'namedDependency'  => $dependency,
        ]);

        /** @var NamedInjection $object */
        $object = $container->get(NamedInjection::class);
        $this->assertSame($dependency, $object->dependency);
    }

    /**
     * @test
     * @expectedException \DI\DependencyException
     */
    public function errors_if_dependency_by_name_not_found()
    {
        $container = $this->createContainer();
        $container->get(NamedInjection::class);
    }

    /**
     * Check that @ var annotation takes "use" statements into account.
     * @test
     * @link https://github.com/PHP-DI/PHP-DI/issues/1
     */
    public function resolve_class_names_using_import_statements()
    {
        $container = $this->createContainer();

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
     * @expectedException \PhpDocReader\AnnotationException
     */
    public function testNotFoundVarAnnotation()
    {
        $container = $this->createContainer();
        $container->get(NotFoundVarAnnotation::class);
    }

    private function createContainer(array $definitions = [])
    {
        $builder = new ContainerBuilder;
        $builder->useAnnotations(true);
        if ($definitions) {
            $builder->addDefinitions($definitions);
        }

        return $builder->build();
    }
}
