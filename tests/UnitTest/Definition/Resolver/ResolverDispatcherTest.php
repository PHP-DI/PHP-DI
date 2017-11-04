<?php

declare(strict_types=1);

namespace DI\Test\UnitTest\Definition\Resolver;

use DI\Definition\Definition;
use DI\Definition\Resolver\ResolverDispatcher;
use DI\Definition\StringDefinition;
use DI\Definition\ValueDefinition;
use DI\Proxy\ProxyFactory;
use EasyMock\EasyMock;
use Psr\Container\ContainerInterface;

/**
 * @covers \DI\Definition\Resolver\ResolverDispatcher
 */
class ResolverDispatcherTest extends \PHPUnit_Framework_TestCase
{
    use EasyMock;

    private $container;
    private $proxyFactory;
    /**
     * @var ResolverDispatcher
     */
    private $resolver;

    public function setUp()
    {
        $this->container = $this->easyMock(ContainerInterface::class);
        $this->proxyFactory = $this->easyMock(ProxyFactory::class);
        $this->resolver = new ResolverDispatcher($this->container, $this->proxyFactory);
    }

    /**
     * @test
     */
    public function should_resolve_using_sub_resolvers()
    {
        $this->assertEquals('foo', $this->resolver->resolve(new ValueDefinition('foo')));
        $this->assertEquals('bar', $this->resolver->resolve(new StringDefinition('bar')));
    }

    /**
     * @test
     */
    public function should_test_if_resolvable_using_sub_resolvers()
    {
        $this->assertTrue($this->resolver->isResolvable(new ValueDefinition('value')));
        $this->assertTrue($this->resolver->isResolvable(new StringDefinition('value')));
    }

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessage No definition resolver was configured for definition of type
     */
    public function should_throw_if_non_handled_definition()
    {
        $this->resolver->resolve($this->easyMock(Definition::class));
    }

    /**
     * @test
     */
    public function should_resolve_definitions()
    {
        $definition = new ValueDefinition('bar');

        $this->assertTrue($this->resolver->isResolvable($definition));
        $this->assertEquals('bar', $this->resolver->resolve($definition));
    }
}
