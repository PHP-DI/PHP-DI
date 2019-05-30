<?php

declare(strict_types=1);

namespace DI\Test\IntegrationTest\Definitions;

use DI\ContainerBuilder;
use DI\ServiceLocator;
use DI\Test\IntegrationTest\BaseContainerTest;
use function DI\autowire;

/**
 * Test service locator definitions.
 */
class ServiceLocatorDefinitionTest extends BaseContainerTest
{
    /**
     * @dataProvider provideContainer
     */
    public function test_service_locator(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            ServiceLocatorDefinitionTest\TestClass::class => autowire()
        ]);
        $container = $builder->build();

        self::assertEntryIsCompiled($container, ServiceLocatorDefinitionTest\TestClass::class);

        $instance = $container->get(ServiceLocatorDefinitionTest\TestClass::class);
        $this->assertInstanceOf(ServiceLocator::class, $instance->serviceLocator);
        $this->assertEquals(ServiceLocatorDefinitionTest\TestClass::class, $instance->serviceLocator->getSubscriber());
        $this->assertEquals(['foo' => 'foo'], $instance->serviceLocator->getServices());
    }
}

namespace DI\Test\IntegrationTest\Definitions\ServiceLocatorDefinitionTest;

use DI\ServiceLocator;
use DI\ServiceSubscriberInterface;

class TestClass implements ServiceSubscriberInterface
{
    public $serviceLocator;

    public function __construct(ServiceLocator $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    public static function getSubscribedServices(): array
    {
        return ['foo'];
    }
}