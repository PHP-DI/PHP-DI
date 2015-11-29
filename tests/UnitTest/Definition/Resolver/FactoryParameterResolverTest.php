<?php

namespace DI\Test\UnitTest\Definition\Resolver;

use DI\Factory\RequestedEntry;
use DI\Invoker\FactoryParameterResolver;
use EasyMock\EasyMock;
use Interop\Container\ContainerInterface;

/**
 * @covers \DI\Invoker\FactoryParameterResolver
 */
class FactoryParameterResolverTest extends \PHPUnit_Framework_TestCase
{
    use EasyMock;

    /**
     * @var FactoryParameterResolver
     */
    private $resolver;

    /**
     * @var ContainerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $container;

    /**
     * @var RequestedEntry|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestedEntry;

    public function setUp()
    {
        $this->container = $this->easyMock('Interop\Container\ContainerInterface');
        $this->resolver = new FactoryParameterResolver($this->container);
        $this->requestedEntry = $this->easyMock('DI\Factory\RequestedEntry');
    }

    /**
     * @test
     */
    public function should_resolve_container_and_requested_entry()
    {
        $callable = function (ContainerInterface $c, RequestedEntry $entry) {};
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
        $callable = function (ContainerInterface $c) {};
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
        $callable = function (RequestedEntry $entry) {};
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
        $callable = function () {};
        $reflection = new \ReflectionFunction($callable);

        $parameters = $this->resolver->getParameters($reflection, [$this->container, $this->requestedEntry], []);

        $this->assertCount(0, $parameters);
    }
}
