<?php

declare(strict_types=1);

namespace DI\Test\IntegrationTest\Attributes;

use DI\ContainerBuilder;
use DI\Test\IntegrationTest\BaseContainerTest;
use DI\DependencyException;

/**
 * Test using PHP 8 attributes.
 *
 * @requires PHP >= 8
 */
#[\PHPUnit\Framework\Attributes\RequiresPhp('>= 8')]
class AttributesTest extends BaseContainerTest
{
    /**
     * @test
     * @dataProvider provideContainer
     */
    #[\PHPUnit\Framework\Attributes\Test]
    #[\PHPUnit\Framework\Attributes\DataProvider('provideContainer')]
    public function inject_in_properties(ContainerBuilder $builder)
    {
        $builder->useAttributes(true);

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
    #[\PHPUnit\Framework\Attributes\Test]
    #[\PHPUnit\Framework\Attributes\DataProvider('provideContainer')]
    public function inject_in_parent_properties(ContainerBuilder $builder)
    {
        $builder->useAttributes(true);
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
    #[\PHPUnit\Framework\Attributes\Test]
    #[\PHPUnit\Framework\Attributes\DataProvider('provideContainer')]
    public function inject_in_private_parent_properties_with_same_name(ContainerBuilder $builder)
    {
        $builder->useAttributes(true);
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
    #[\PHPUnit\Framework\Attributes\Test]
    #[\PHPUnit\Framework\Attributes\DataProvider('provideContainer')]
    public function inject_by_name(ContainerBuilder $builder)
    {
        $builder->useAttributes(true);

        $dependency = new \stdClass();

        $builder->addDefinitions([
            'namedDependency'  => $dependency,
        ]);
        $container = $builder->build();

        /** @var NamedInjection $object */
        $object = $container->get(NamedInjection::class);
        $this->assertSame($dependency, $object->dependency1);
        $this->assertSame($dependency, $object->dependency2);
    }

    /**
     * @test
     * @dataProvider provideContainer
     */
    #[\PHPUnit\Framework\Attributes\Test]
    #[\PHPUnit\Framework\Attributes\DataProvider('provideContainer')]
    public function errors_if_dependency_by_name_not_found(ContainerBuilder $builder)
    {
        $this->expectException(DependencyException::class);
        $builder->useAttributes(true);
        $builder->build()->get(NamedInjection::class);
    }

    /**
     * @test
     * @dataProvider provideContainer
     */
    #[\PHPUnit\Framework\Attributes\Test]
    #[\PHPUnit\Framework\Attributes\DataProvider('provideContainer')]
    public function inject_promoted_property(ContainerBuilder $builder)
    {
        $builder->useAttributes(true);
        $object = $builder->build()->get(PromotedProperty::class);
        $this->assertInstanceOf(A::class, $object->promotedProperty);
    }

    /**
     * @test
     * @dataProvider provideContainer
     */
    #[\PHPUnit\Framework\Attributes\Test]
    #[\PHPUnit\Framework\Attributes\DataProvider('provideContainer')]
    public function inject_promoted_readonly_property(ContainerBuilder $builder)
    {
        if (PHP_VERSION_ID < 80100) {
            $this->markTestSkipped("PHP 8.1 required for readonly properties");
        }
        $builder->useAttributes(true);
        $object = $builder->build()->get(PromotedReadonlyProperty::class);
        $this->assertInstanceOf(A::class, $object->promotedProperty);
    }
}
