<?php

declare(strict_types=1);

namespace DI\Test\IntegrationTest\Definitions;

use DI\Annotation\Inject;
use DI\ContainerBuilder;
use DI\Test\IntegrationTest\BaseContainerTest;
use DI\Test\IntegrationTest\Fixtures\Implementation1;
use DI\Test\IntegrationTest\Fixtures\Implementation2;
use DI\Test\IntegrationTest\Fixtures\Interface1;
use DI\Test\IntegrationTest\Fixtures\Interface2;

/**
 * Test definitions using wildcards.
 */
class WildcardDefinitionsTest extends BaseContainerTest
{
    /**
     * @dataProvider provideContainer
     */
    public function test_wildcards(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            'foo*' => 'bar',
            'DI\Test\IntegrationTest\*\Interface*' => \DI\create('DI\Test\IntegrationTest\*\Implementation*'),
        ]);
        $container = $builder->build();

        $this->assertEquals('bar', $container->get('foo1'));

        $object = $container->get(Interface1::class);
        $this->assertInstanceOf(Implementation1::class, $object);

        self::assertEntryIsNotCompiled($container, 'foo*');
        self::assertEntryIsNotCompiled($container, 'foo1');
        self::assertEntryIsNotCompiled($container, 'DI\Test\IntegrationTest\*\Interface*');
        self::assertEntryIsNotCompiled($container, Interface1::class);
    }
    /**
     * @dataProvider provideContainer
     */
    public function test_wildcard_with_static_name(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            'DI\Test\IntegrationTest\*\Interface*' => \DI\create(Implementation1::class),
        ]);
        $container = $builder->build();

        $object = $container->get(Interface1::class);
        $this->assertInstanceOf(Implementation1::class, $object);

        self::assertEntryIsNotCompiled($container, Interface1::class);
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_wildcards_autowire(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            'DI\Test\IntegrationTest\*\Interface*' => \DI\autowire('DI\Test\IntegrationTest\*\Implementation*'),
        ]);
        $container = $builder->build();

        $object = $container->get(Interface1::class);
        $this->assertInstanceOf(Implementation1::class, $object);

        self::assertEntryIsNotCompiled($container, 'DI\Test\IntegrationTest\*\Interface*');
        self::assertEntryIsNotCompiled($container, Interface1::class);
        self::assertEntryIsNotCompiled($container, Interface2::class);
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_wildcards_autowire_with_dependency(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            'DI\Test\IntegrationTest\*\Interface*' => \DI\autowire('DI\Test\IntegrationTest\*\Implementation*'),
        ]);
        $container = $builder->build();

        $object = $container->get(Interface1::class);
        $this->assertInstanceOf(Implementation1::class, $object);

        $object2 = $container->get(Interface2::class);
        $this->assertInstanceOf(Implementation2::class, $object2);
        $this->assertInstanceOf(Implementation1::class, $object2->dependency);

        self::assertEntryIsNotCompiled($container, 'DI\Test\IntegrationTest\*\Interface*');
        self::assertEntryIsNotCompiled($container, Interface1::class);
        self::assertEntryIsNotCompiled($container, Interface2::class);
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_wildcards_as_dependency(ContainerBuilder $builder)
    {
        $builder->useAnnotations(true);
        $builder->addDefinitions([
            'DI\Test\IntegrationTest\*\Interface*' => \DI\create('DI\Test\IntegrationTest\*\Implementation*'),
        ]);
        $container = $builder->build();

        /** @var WildcardDefinitionsTestFixture $object */
        $object = $container->get(WildcardDefinitionsTestFixture::class);
        $this->assertInstanceOf(Implementation1::class, $object->dependency);
    }
}

class WildcardDefinitionsTestFixture
{
    /**
     * @Inject
     */
    public Interface1 $dependency;
}
