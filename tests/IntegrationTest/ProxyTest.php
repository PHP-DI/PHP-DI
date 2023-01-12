<?php

declare(strict_types=1);

namespace DI\Test\IntegrationTest;

use DI\ContainerBuilder;
use DI\Test\IntegrationTest\Fixtures\LazyDependency;
use DI\Test\IntegrationTest\Fixtures\ProxyTest\A;
use DI\Test\IntegrationTest\Fixtures\ProxyTest\B;
use ProxyManager\Proxy\LazyLoadingInterface;

/**
 * Test lazy injections with proxies.
 */
class ProxyTest extends BaseContainerTest
{
    /**
     * @test
     * @dataProvider provideContainer
     */
    public function container_can_create_lazy_objects(ContainerBuilder $builder)
    {
        $builder->useAutowiring(false);
        $builder->addDefinitions([
            'foo' => \DI\create(LazyDependency::class)
                ->lazy(),
        ]);

        $proxy = $builder->build()->get('foo');
        $this->assertInstanceOf(LazyLoadingInterface::class, $proxy);
        $this->assertInstanceOf(LazyDependency::class, $proxy);
    }

    /**
     * @test
     * @dataProvider provideContainer
     */
    public function lazy_services_resolve_to_the_same_instance(ContainerBuilder $builder)
    {
        $builder->useAutowiring(false);
        $builder->addDefinitions([
            'foo' => \DI\create(LazyDependency::class)
                ->lazy(),
        ]);
        $container = $builder->build();

        /** @var LazyDependency $proxy */
        $proxy = $container->get('foo');
        $this->assertSame($proxy, $container->get('foo'));
        // Resolve the proxy and check again
        /** @noinspection PhpExpressionResultUnusedInspection */
        $proxy->getValue();
        $this->assertSame($proxy, $container->get('foo'));
    }

    /**
     * @test
     * @dataProvider provideContainer
     */
    public function dependencies_of_proxies_are_resolved_once(ContainerBuilder $builder)
    {
        $builder->useAutowiring(false);
        $builder->addDefinitions([
            'A' => \DI\create(A::class)
                ->constructor(\DI\get('B'))
                ->lazy(),
            'B' => \DI\create(B::class),
        ]);
        $container = $builder->build();

        /** @var A $a1 */
        $a1 = $container->get('A');
        /** @var A $a2 */
        $a2 = $container->get('A');
        $this->assertSame($a1->getB(), $a2->getB());
    }
}
