<?php

namespace DI\Test\IntegrationTest;

use DI\ContainerBuilder;
use DI\Test\IntegrationTest\Fixtures\LazyDependency;
use DI\Test\IntegrationTest\Fixtures\ProxyTest\A;

/**
 * Test lazy injections with proxies.
 *
 * @coversNothing
 */
class ProxyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function container_can_create_lazy_objects()
    {
        $container = $this->createContainer([
            'foo' => \DI\object('DI\Test\IntegrationTest\Fixtures\LazyDependency')
                ->lazy(),
        ]);

        $proxy = $container->get('foo');
        $this->assertInstanceOf('ProxyManager\Proxy\LazyLoadingInterface', $proxy);
        $this->assertInstanceOf('DI\Test\IntegrationTest\Fixtures\LazyDependency', $proxy);
    }

    /**
     * @test
     */
    public function lazy_singletons_resolve_to_the_same_instance()
    {
        $container = $this->createContainer([
            'foo' => \DI\object('DI\Test\IntegrationTest\Fixtures\LazyDependency')
                ->lazy(),
        ]);

        /** @var LazyDependency $proxy */
        $proxy = $container->get('foo');
        $this->assertSame($proxy, $container->get('foo'));
        // Resolve the proxy and check again
        $proxy->getValue();
        $this->assertSame($proxy, $container->get('foo'));
    }

    /**
     * @test
     */
    public function singleton_dependencies_of_proxies_are_resolved_once()
    {
        $container = $this->createContainer([
            'A' => \DI\object('DI\Test\IntegrationTest\Fixtures\ProxyTest\A')
                ->constructor(\DI\get('B'))
                ->lazy(),
            'B' => \DI\object('DI\Test\IntegrationTest\Fixtures\ProxyTest\B'),
        ]);

        /** @var A $a1 */
        $a1 = $container->get('A');
        /** @var A $a2 */
        $a2 = $container->get('A');
        $this->assertSame($a1->getB(), $a2->getB());
    }

    private function createContainer(array $definitions)
    {
        $builder = new ContainerBuilder;
        $builder->useAutowiring(false);
        $builder->addDefinitions($definitions);

        return $builder->build();
    }
}
