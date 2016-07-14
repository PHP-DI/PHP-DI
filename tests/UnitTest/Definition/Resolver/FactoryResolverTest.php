<?php

namespace DI\Test\UnitTest\Definition\Resolver;

use DI\Definition\FactoryDefinition;
use DI\Definition\Resolver\FactoryResolver;
use DI\NotFoundException;
use EasyMock\EasyMock;
use Interop\Container\ContainerInterface;

/**
 * @covers \DI\Definition\Resolver\FactoryResolver
 */
class FactoryResolverTest extends \PHPUnit_Framework_TestCase
{
    use EasyMock;

    /**
     * @test
     */
    public function should_resolve_callables()
    {
        $container = $this->easyMock(ContainerInterface::class);
        $resolver = new FactoryResolver($container);

        $definition = new FactoryDefinition('foo', function () {
            return 'bar';
        });

        $value = $resolver->resolve($definition);

        $this->assertEquals('bar', $value);
    }

    /**
     * @test
     */
    public function should_inject_container()
    {
        $container = $this->easyMock(ContainerInterface::class);
        $resolver = new FactoryResolver($container);

        $definition = new FactoryDefinition('foo', function ($c) {
            return $c;
        });

        $value = $resolver->resolve($definition);

        $this->assertInstanceOf(ContainerInterface::class, $value);
    }

    /**
     * @test
     * @expectedException \DI\Definition\Exception\DefinitionException
     * @expectedExceptionMessage Entry "foo" cannot be resolved: factory 'Hello world' is neither a callable nor a valid container entry
     */
    public function should_throw_if_the_factory_is_not_callable()
    {
        $container = $this->easyMock(ContainerInterface::class);
        $resolver = new FactoryResolver($container);

        $definition = new FactoryDefinition('foo', 'Hello world');

        $container->expects($this->once())
            ->method('get')
            ->with('Hello world')
            ->willThrowException(new NotFoundException);

        $resolver->resolve($definition);
    }
}
