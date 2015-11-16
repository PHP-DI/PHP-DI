<?php

namespace DI\Test\UnitTest\Definition\Resolver;

use DI\Definition\Definition;
use DI\Invoker\FactoryParameterResolver;
use EasyMock\EasyMock;
use Interop\Container\ContainerInterface;

/**
 * @covers \DI\Invoker\FactoryParameterResolver
 */
class FactoryParameterResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FactoryParameterResolver
     */
    private $resolver;

    /**
     * @var ContainerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $container;

    /**
     * @var Definition|\PHPUnit_Framework_MockObject_MockObject
     */
    private $definition;

    public function setUp()
    {
        $this->resolver = new FactoryParameterResolver;
        $this->container = EasyMock::mock('Interop\Container\ContainerInterface');
        $this->definition = EasyMock::mock('DI\Definition\Definition');
    }

    /**
     * @test
     */
    public function should_resolve_container_and_definition()
    {
        $callable = function (ContainerInterface $c, Definition $d) {};
        $reflection = new \ReflectionFunction($callable);

        $parameters = $this->resolver->getParameters($reflection, [$this->container, $this->definition], []);

        $this->assertCount(2, $parameters);
        $this->assertSame($this->container, $parameters[0]);
        $this->assertSame($this->definition, $parameters[1]);
    }

    /**
     * @test
     */
    public function should_resolve_only_container()
    {
        $callable = function (ContainerInterface $c) {};
        $reflection = new \ReflectionFunction($callable);

        $parameters = $this->resolver->getParameters($reflection, [$this->container, $this->definition], []);

        $this->assertCount(1, $parameters);
        $this->assertSame($this->container, $parameters[0]);
    }

    /**
     * @test
     */
    public function should_resolve_only_definition()
    {
        $callable = function (Definition $d) {};
        $reflection = new \ReflectionFunction($callable);

        $parameters = $this->resolver->getParameters($reflection, [$this->container, $this->definition], []);

        $this->assertCount(1, $parameters);
        $this->assertSame($this->definition, $parameters[0]);
    }

    /**
     * @test
     */
    public function should_resolve_nothing()
    {
        $callable = function () {};
        $reflection = new \ReflectionFunction($callable);

        $parameters = $this->resolver->getParameters($reflection, [$this->container, $this->definition], []);

        $this->assertCount(0, $parameters);
    }
}
