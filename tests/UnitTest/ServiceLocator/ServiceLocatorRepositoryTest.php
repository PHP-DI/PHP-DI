<?php

declare(strict_types=1);

namespace DI\Test\UnitTest;

use DI\ContainerBuilder;
use DI\ServiceLocator;
use DI\ServiceLocatorRepository;
use EasyMock\EasyMock;
use PHPUnit\Framework\TestCase;

/**
 * Test class for ServiceLocatorRepository.
 *
 * @covers \DI\ServiceLocatorRepository
 */
class ServiceLocatorRepositoryTest extends TestCase
{
    use EasyMock;

    public function testCreateServiceLocator()
    {
        $container = ContainerBuilder::buildDevContainer();
        $repository = new ServiceLocatorRepository($container);

        $services = ['SomeServiceClass'];
        $expectedServices = ['SomeServiceClass' => 'SomeServiceClass'];

        $serviceLocator = $repository->create('test', $services);

        $this->assertEquals('test', $serviceLocator->getSubscriber());
        $this->assertEquals($expectedServices, $serviceLocator->getServices());
    }

    /**
     * @expectedException \DI\NotFoundException
     * @expectedExceptionMessage Service locator for entry 'something' is not initialized.
     */
    public function testServiceLocatorNotCreated()
    {
        $container = ContainerBuilder::buildDevContainer();
        $repository = new ServiceLocatorRepository($container);
        $repository->get('something');
    }

    public function testGetServiceLocator()
    {
        $container = ContainerBuilder::buildDevContainer();
        $repository = new ServiceLocatorRepository($container);
        $repository->create('test');

        $this->assertInstanceOf(ServiceLocator::class, $repository->get('test'));
    }

    public function testHasServiceLocator()
    {
        $container = ContainerBuilder::buildDevContainer();
        $repository = new ServiceLocatorRepository($container);
        $repository->create('test');

        $this->assertTrue($repository->has('test'));
        $this->assertFalse($repository->has('something-else'));
    }

    public function testOverrideService()
    {
        $container = ContainerBuilder::buildDevContainer();
        $repository = new ServiceLocatorRepository($container);
        $repository->override('test', 'foo');
        $repository->override('test', 'bar', 'baz');

        $locator = $repository->create('test');
        $this->assertEquals(['foo' => 'foo', 'bar' => 'baz'], $locator->getServices());
    }

    public function testCanCreateMultipleWithSameServices()
    {
        $container = ContainerBuilder::buildDevContainer();
        $repository = new ServiceLocatorRepository($container);
        $locator1 = $repository->create('test', ['foo']);
        $locator2 = $repository->create('test', ['foo']);

        // same instance
        $this->assertSame($locator1, $locator2);

        $repository->override('test2', 'bar', 'baz');
        $locator3 = $repository->create('test2');
        $locator4 = $repository->create('test2');
        $this->assertSame($locator3, $locator4);

        // still same services, because that matches the initial override
        $locator5 = $repository->create('test2', ['bar' => 'baz']);
        $this->assertSame($locator3, $locator5);
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage ServiceLocator for 'test' cannot be recreated with different services.
     */
    public function testCannotCreateMultipleWithDifferentServices()
    {
        $container = ContainerBuilder::buildDevContainer();
        $repository = new ServiceLocatorRepository($container);

        $repository->create('test', ['foo']);
        $repository->create('test', ['foo2']);
    }
}
