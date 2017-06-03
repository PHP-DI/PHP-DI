<?php

namespace DI\Test\UnitTest;

use DI\Container;
use DI\ContainerBuilder;
use DI\FactoryInterface;
use DI\InvokerInterface;
use DI\Test\IntegrationTest\BaseContainerTest;
use Psr\Container\ContainerInterface;

/**
 * Test entries registered by default.
 */
class DefaultEntriesTest extends BaseContainerTest
{
    /**
     * The container auto-registers itself.
     * @dataProvider provideContainer
     */
    public function testContainerIsRegistered(ContainerBuilder $builder)
    {
        $container = $builder->build();

        $this->assertSame($container, $container->get(Container::class));
    }

    /**
     * The container auto-registers itself (with the factory interface).
     * @dataProvider provideContainer
     */
    public function testFactoryInterfaceIsRegistered(ContainerBuilder $builder)
    {
        $container = $builder->build();

        $this->assertSame($container, $container->get(FactoryInterface::class));
    }

    /**
     * The container auto-registers itself (with the invoker interface).
     * @dataProvider provideContainer
     */
    public function testInvokerInterfaceIsRegistered(ContainerBuilder $builder)
    {
        $container = $builder->build();

        $this->assertSame($container, $container->get(InvokerInterface::class));
    }

    /**
     * The container auto-registers itself (with the container interface).
     * @dataProvider provideContainer
     */
    public function testContainerInterfaceIsRegistered(ContainerBuilder $builder)
    {
        $container = $builder->build();

        $this->assertSame($container, $container->get(ContainerInterface::class));
    }
}
