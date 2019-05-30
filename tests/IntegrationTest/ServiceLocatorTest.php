<?php

declare(strict_types=1);

namespace DI\Test\IntegrationTest;

use DI\ContainerBuilder;
use function DI\autowire;
use DI\ServiceLocatorRepository;

/**
 * Test service locators for service subscribers.
 */
class ServiceLocatorTest extends BaseContainerTest
{
    /**
     * @dataProvider provideContainer
     */
    public function testServiceLocator(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            'foo' => 'value of foo',
            'baz' => 'baz',
        ]);

        $container = $builder->build();
        $instance = $container->get(ServiceLocatorTest\ServiceSubscriber::class);
        $this->assertEquals('value of foo', $instance->getFoo());
        $this->assertEquals('baz', $instance->getBar());
        $this->assertInstanceOf(ServiceLocatorTest\SomeService::class, $instance->getClass());
    }

    /**
     * @dataProvider provideContainer
     * @expectedException \DI\NotFoundException
     * @expectedExceptionMessage Service 'baz' is not defined.
     */
    public function testServiceLocatorThrowsForInvalidService(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            'baz' => 'baz',
        ]);

        $container = $builder->build();
        $instance = $container->get(ServiceLocatorTest\ServiceSubscriber::class);
        $instance->getInvalid();
    }

    /**
     * @dataProvider provideContainer
     */
    public function testServicesLazyResolve(ContainerBuilder $builder)
    {
        $container = $builder->build();

        // services should not be resolved on instantiation of a subscriber class
        $instance = $container->get(ServiceLocatorTest\ServiceSubscriber::class);
        $this->assertNotContains(ServiceLocatorTest\SomeService::class, $container->getKnownEntryNames());

        // resolve on demand
        $instance->getClass();
        $this->assertContains(ServiceLocatorTest\SomeService::class, $container->getKnownEntryNames());
    }

    /**
     * @dataProvider provideContainer
     */
    public function testOverrideService(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            'foo' => 'foo',
            'baz' => 'baz',
            'anotherFoo' => 'overridden foo',
        ]);
        $container = $builder->build();
        $repository = $container->get(ServiceLocatorRepository::class);
        $repository->override(ServiceLocatorTest\ServiceSubscriber::class, 'foo', 'anotherFoo');

        $instance = $container->get(ServiceLocatorTest\ServiceSubscriber::class);
        $this->assertEquals('overridden foo', $instance->getFoo());
    }

    /**
     * @dataProvider provideContainer
     */
    public function testOverrideServiceInRepositoryDefinition(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            ServiceLocatorRepository::class => autowire()
                ->method('override', ServiceLocatorTest\ServiceSubscriber::class, 'foo', 'anotherFoo'),
            'anotherFoo' => 'overridden foo',
        ]);

        $container = $builder->build();

        $instance = $container->get(ServiceLocatorTest\ServiceSubscriber::class);
        $this->assertEquals('overridden foo', $instance->getFoo());
    }

    /**
     * @dataProvider provideContainer
     * @expectedException \LogicException
     * @expectedExceptionMessage Service 'foo' for 'DI\Test\IntegrationTest\ServiceLocatorTest\ServiceSubscriber' cannot be overridden - ServiceLocator is already created.
     */
    public function testCannotOverrideServiceForAlreadyInstantiatedSubscriber(ContainerBuilder $builder)
    {
        $container = $builder->build();

        $container->get(ServiceLocatorTest\ServiceSubscriber::class);

        $repository = $container->get(ServiceLocatorRepository::class);
        $repository->override(ServiceLocatorTest\ServiceSubscriber::class, 'foo', 'anotherFoo');
    }

    /**
     * @dataProvider provideContainer
     */
    public function testMultipleSubscriberInstances(ContainerBuilder $builder)
    {
        $container = $builder->build();
        $instance1 = $container->make(ServiceLocatorTest\ServiceSubscriber::class);
        $instance2 = $container->make(ServiceLocatorTest\ServiceSubscriber::class);

        // different instances
        $this->assertNotSame($instance1, $instance2);
        // but the same service locator instance
        $this->assertSame($instance1->getServiceLocator(), $instance2->getServiceLocator());
        // and an instance of a service should be shared too
        $this->assertSame($instance1->getClass(), $instance2->getClass());
    }

}

namespace DI\Test\IntegrationTest\ServiceLocatorTest;

use DI\ServiceLocator;
use DI\ServiceSubscriberInterface;

/**
 * Fixture class for testing service locators
 */
class ServiceSubscriber implements ServiceSubscriberInterface
{
    /**
     * @var ServiceLocator
     */
    protected $serviceLocator;

    /**
     * @param ServiceLocator $serviceLocator
     */
    public function __construct(ServiceLocator $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    /**
     * Lazy instantiate heavy dependencies on-demand
     */
    public static function getSubscribedServices(): array
    {
        return [
            'foo',
            'bar' => 'baz',
            SomeService::class,
        ];
    }

    public function getFoo()
    {
        return $this->serviceLocator->get('foo');
    }

    public function getBar()
    {
        return $this->serviceLocator->get('bar');
    }

    public function getClass()
    {
        return $this->serviceLocator->get(SomeService::class);
    }

    /**
     * @throws \DI\NotFoundException
     */
    public function getInvalid()
    {
        return $this->serviceLocator->get('baz');
    }

    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }
}

class SomeService
{
}
