<?php

declare(strict_types=1);

namespace DI\Test\IntegrationTest;

use DI\ContainerBuilder;
use function DI\create;

/**
 * Tests the set() method from the container.
 */
class ContainerSetTest extends BaseContainerTest
{
    /**
     * We should be able to set a null value.
     * @see https://github.com/mnapoli/PHP-DI/issues/79
     * @dataProvider provideContainer
     */
    public function testSetNullValue(ContainerBuilder $builder)
    {
        $container = $builder->build();
        $container->set('foo', null);

        $this->assertNull($container->get('foo'));
    }

    /**
     * @see https://github.com/mnapoli/PHP-DI/issues/126
     * @test
     * @dataProvider provideContainer
     */
    public function testSetGetSetGet(ContainerBuilder $builder)
    {
        $container = $builder->build();

        $container->set('foo', 'bar');
        $container->get('foo');
        $container->set('foo', 'hello');

        $this->assertSame('hello', $container->get('foo'));
    }

    /**
     * @test
     * @dataProvider provideContainer
     */
    public function array_entries_can_be_overridden_by_values(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            'foo' => 'bar',
        ]);
        $container = $builder->build();

        $container->set('foo', 'hello');

        $this->assertSame('hello', $container->get('foo'));
    }

    /**
     * @test
     * @dataProvider provideContainer
     */
    public function array_entries_can_be_overridden_by_definitions(ContainerBuilder $builder)
    {
        if ($builder->isCompiled()) {
            // This behavior is not allowed on the compiled container
            return;
        }

        $builder->addDefinitions([
            'foo' => create(\stdClass::class),
        ]);
        $container = $builder->build();

        $container->set('foo', create(ContainerSetTest\Dummy::class));

        $this->assertInstanceOf(ContainerSetTest\Dummy::class, $container->get('foo'));
    }
}

namespace DI\Test\IntegrationTest\ContainerSetTest;

class Dummy
{
}
