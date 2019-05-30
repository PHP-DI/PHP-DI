<?php

declare(strict_types=1);

namespace DI\Test\UnitTest\Definition;

use DI\Definition\ServiceLocatorDefinition;
use DI\ServiceLocator;
use DI\Test\UnitTest\Fixtures\Singleton;
use EasyMock\EasyMock;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * @covers \DI\Definition\ServiceLocatorDefinition
 */
class ServiceLocatorDefinitionTest extends TestCase
{
    use EasyMock;

    /**
     * @test
     */
    public function should_have_a_name_and_requesting_name()
    {
        $definition = new ServiceLocatorDefinition('ServiceLocator', 'subscriber');
        $this->assertEquals('ServiceLocator', $definition->getName());
        $definition->setName('foo');
        $this->assertEquals('foo', $definition->getName());

        $this->assertEquals('subscriber', $definition->getRequestingName());
    }

    /**
     * @test
     * @expectedException \DI\ServiceSubscriberException
     * @expectedExceptionMessage The class DI\Test\UnitTest\Fixtures\Singleton does not implement ServiceSubscriberInterface.
     */
    public function cannot_resolve_without_proper_subscriber()
    {
        $container = $this->easyMock(ContainerInterface::class);
        $definition = new ServiceLocatorDefinition(ServiceLocator::class, Singleton::class);

        $this->assertFalse($definition->isResolvable($container));
        $definition->resolve($container);
    }

    /**
     * @test
     */
    public function should_cast_to_string()
    {
        $definition = new ServiceLocatorDefinition('bar', 'subscriber');
        $this->assertEquals("get(bar) for 'subscriber'", (string) $definition);
    }
}
