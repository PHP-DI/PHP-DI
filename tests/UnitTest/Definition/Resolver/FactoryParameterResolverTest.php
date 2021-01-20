<?php

declare(strict_types=1);

namespace DI\Test\UnitTest\Definition\Resolver;

use DI\Container;
use DI\Factory\RequestedEntry;
use DI\Invoker\FactoryParameterResolver;
use DI\Test\UnitTest\Definition\Resolver\Fixture\NoConstructor;
use EasyMock\EasyMock;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * @covers \DI\Invoker\FactoryParameterResolver
 */
class FactoryParameterResolverTest extends TestCase
{
    use EasyMock;

    /**
     * @var FactoryParameterResolver
     */
    private $resolver;

    /**
     * @var ContainerInterface|MockObject
     */
    private $container;

    /**
     * @var RequestedEntry|MockObject
     */
    private $requestedEntry;

    public function setUp(): void
    {
        $this->container = $this->easyMock(ContainerInterface::class);
        $this->resolver = new FactoryParameterResolver($this->container);
        $this->requestedEntry = $this->easyMock(RequestedEntry::class);
    }

    /**
     * @test
     */
    public function should_resolve_psr11_container()
    {
        $callable = function (ContainerInterface $c) {
        };
        $reflection = new \ReflectionFunction($callable);

        $parameters = $this->resolver->getParameters($reflection, [$this->container, $this->requestedEntry], []);

        $this->assertCount(1, $parameters);
        $this->assertSame($this->container, $parameters[0]);
    }

    /**
     * @test
     */
    public function should_resolve_container_and_requested_entry()
    {
        $callable = function (ContainerInterface $c, RequestedEntry $entry) {
        };
        $reflection = new \ReflectionFunction($callable);

        $parameters = $this->resolver->getParameters($reflection, [$this->container, $this->requestedEntry], []);

        $this->assertCount(2, $parameters);
        $this->assertSame($this->container, $parameters[0]);
        $this->assertSame($this->requestedEntry, $parameters[1]);
    }

    /**
     * @test
     */
    public function should_resolve_only_container()
    {
        $callable = function (ContainerInterface $c) {
        };
        $reflection = new \ReflectionFunction($callable);

        $parameters = $this->resolver->getParameters($reflection, [$this->container, $this->requestedEntry], []);

        $this->assertCount(1, $parameters);
        $this->assertSame($this->container, $parameters[0]);
    }

    /**
     * @test
     */
    public function should_resolve_only_requested_entry()
    {
        $callable = function (RequestedEntry $entry) {
        };
        $reflection = new \ReflectionFunction($callable);

        $parameters = $this->resolver->getParameters($reflection, [$this->container, $this->requestedEntry], []);

        $this->assertCount(1, $parameters);
        $this->assertSame($this->requestedEntry, $parameters[0]);
    }

    /**
     * @test
     */
    public function should_resolve_nothing()
    {
        $callable = function () {
        };
        $reflection = new \ReflectionFunction($callable);

        $parameters = $this->resolver->getParameters($reflection, [$this->container, $this->requestedEntry], []);

        $this->assertCount(0, $parameters);
    }

    /**
     * @test
     */
    public function should_not_overwrite_resolved_with_container_or_entry()
    {
        $callable = function (ContainerInterface $container, RequestedEntry $entry, $other) {
        };
        $reflection = new \ReflectionFunction($callable);

        $mockContainer = $this->easyMock(Container::class);
        $mockEntry = $this->easyMock(RequestedEntry::class);

        $resolvedParams = [$mockContainer, $mockEntry, 'Foo'];

        $parameters = $this->resolver->getParameters($reflection, [$this->container, $this->requestedEntry], $resolvedParams);

        $this->assertCount(3, $parameters);
        $this->assertSame($parameters[0], $mockContainer);
        $this->assertSame($parameters[1], $mockEntry);
        $this->assertEquals($parameters[2], 'Foo');
    }

    /**
     * @test
     */
    public function should_not_overwrite_resolved_from_container()
    {
        $callable = function (NoConstructor $nc) {
        };
        $reflection = new \ReflectionFunction($callable);

        $ncMock = $this->easyMock(NoConstructor::class);
        $ncReal = new NoConstructor();

        $preparedContainer = clone $this->container;
        $preparedContainer->expects($this->once())
                          ->method('has')
                          ->with(NoConstructor::class)
                          ->willReturn(true);
        $preparedContainer->expects($this->once())
                          ->method('get')
                          ->with(NoConstructor::class)
                          ->willReturn($ncReal);

        $resolvedParams = [$ncMock];
        $parameters = $this->resolver->getParameters($reflection, [$this->container, $this->requestedEntry], $resolvedParams);

        $this->assertCount(1, $parameters);
        $this->assertSame($parameters[0], $ncMock);
    }
}
