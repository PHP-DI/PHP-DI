<?php

namespace DI\Test\UnitTest\Proxy;

use DI\Proxy\ProxyFactory;
use DI\Test\UnitTest\Proxy\Fixtures\ClassToProxy;

/**
 * @covers \DI\Proxy\ProxyFactory
 */
class ProxyFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function should_create_lazy_proxies()
    {
        $factory = new ProxyFactory(false);

        $instance = new ClassToProxy();
        $initialized = false;

        $initializer = function (&$wrappedObject, $proxy, $method, $parameters, &$initializer) use (
            $instance,
            &$initialized
        ) {
            $wrappedObject = $instance;
            $initializer = null; // turning off further lazy initialization
            $initialized = true;

            return true;
        };
        /** @var ClassToProxy $proxy */
        $proxy = $factory->createProxy(ClassToProxy::class, $initializer);

        $this->assertFalse($initialized);
        $this->assertInstanceOf(ClassToProxy::class, $proxy);

        $proxy->foo();

        $this->assertTrue($initialized);
        $this->assertSame($instance, $proxy->getInstance());
    }
}
