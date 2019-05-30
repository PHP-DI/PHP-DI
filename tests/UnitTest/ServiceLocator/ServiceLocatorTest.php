<?php

declare(strict_types=1);

namespace DI\Test\UnitTest;

use DI\ContainerBuilder;
use DI\ServiceLocator;
use DI\Test\UnitTest\Fixtures\Singleton;
use PHPUnit\Framework\TestCase;

/**
 * Test class for ServiceLocator.
 *
 * @covers \DI\ServiceLocator
 */
class ServiceLocatorTest extends TestCase
{
    public function testInstantiation()
    {
        $container = ContainerBuilder::buildDevContainer();

        $services = [
            'foo' => 'bar',
            'baz',
        ];
        $serviceLocator = new ServiceLocator($container, $services, 'test');

        $this->assertEquals([
            'foo' => 'bar',
            'baz' => 'baz',
        ], $serviceLocator->getServices());
        $this->assertEquals('test', $serviceLocator->getSubscriber());
    }

    /**
     * @expectedException \DI\NotFoundException
     * @expectedExceptionMessage Service 'something' is not defined.
     */
    public function testServiceNotDefined()
    {
        $container = ContainerBuilder::buildDevContainer();
        $serviceLocator = new ServiceLocator($container, [], 'test');
        $serviceLocator->get('something');
    }

    public function testGetService()
    {
        $services = [
            'stdClass',
            'service' => Singleton::class,
        ];
        $services2 = [
            Singleton::class,
        ];

        $container = ContainerBuilder::buildDevContainer();
        $serviceLocator = new ServiceLocator($container, $services, 'test');
        $serviceLocator2 = new ServiceLocator($container, $services2, 'test2');

        $this->assertInstanceOf('stdClass', $serviceLocator->get('stdClass'));

        $service1 = $serviceLocator->get('service');
        $this->assertInstanceOf(Singleton::class, $service1);

        $service2 = $serviceLocator2->get(Singleton::class);
        $this->assertInstanceOf(Singleton::class, $service2);

        // it should be the same instances shared from the container
        $this->assertSame($service1, $service2);
    }

    public function testHasService()
    {
        $services = [
            'service' => Singleton::class,
        ];

        $container = ContainerBuilder::buildDevContainer();
        $serviceLocator = new ServiceLocator($container, $services, 'test');

        $this->assertTrue($serviceLocator->has('service'));
        $this->assertFalse($serviceLocator->has(Singleton::class));
    }
}
