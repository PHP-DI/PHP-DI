<?php

declare(strict_types=1);

namespace DI\Test\UnitTest\Proxy;

use DI\Proxy\ProxyFactory;
use DI\Test\UnitTest\Proxy\Fixtures\ClassToProxy;
use PHPUnit\Framework\TestCase;

/**
 * @covers \DI\Proxy\ProxyFactory
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\DI\Proxy\ProxyFactory::class)]
class ProxyFactoryTest extends TestCase
{
    /**
     * @test
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function should_create_lazy_proxies()
    {
        $factory = new ProxyFactory;

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
