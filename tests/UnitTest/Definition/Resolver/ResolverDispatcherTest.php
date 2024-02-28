<?php

declare(strict_types=1);

namespace DI\Test\UnitTest\Definition\Resolver;

use DI\Definition\DefinitionInterface;
use DI\Definition\Resolver\ResolverDispatcher;
use DI\Definition\StringDefinition;
use DI\Definition\ValueDefinition;
use DI\Proxy\ProxyFactory;
use EasyMock\EasyMock;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * @covers \DI\Definition\Resolver\ResolverDispatcher
 */
class ResolverDispatcherTest extends TestCase
{
    use EasyMock;

    private ResolverDispatcher $resolver;

    public function setUp(): void
    {
        $container = $this->easyMock(ContainerInterface::class);
        $proxyFactory = $this->easyMock(ProxyFactory::class);
        $this->resolver = new ResolverDispatcher($container, $proxyFactory);
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
     */
    public function should_throw_if_non_handled_definition()
    {
        $this->expectException('RuntimeException');
        $this->expectExceptionMessage('No definition resolver was configured for definition of type');
        $this->resolver->resolve($this->easyMock(DefinitionInterface::class));
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
