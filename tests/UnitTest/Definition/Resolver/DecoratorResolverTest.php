<?php

namespace DI\Test\UnitTest\Definition\Resolver;

use DI\Definition\DecoratorDefinition;
use DI\Definition\Resolver\DecoratorResolver;
use DI\Definition\Resolver\DefinitionResolver;
use DI\Definition\ValueDefinition;
use EasyMock\EasyMock;
use Interop\Container\ContainerInterface;
use PHPUnit_Framework_MockObject_MockObject;

/**
 * @covers \DI\Definition\Resolver\DecoratorResolver
 */
class DecoratorResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DecoratorResolver
     */
    private $resolver;

    /**
     * @var DefinitionResolver|PHPUnit_Framework_MockObject_MockObject
     */
    private $parentResolver;

    public function setUp()
    {
        $container = EasyMock::mock('Interop\Container\ContainerInterface');
        $this->parentResolver = EasyMock::mock('DI\Definition\Resolver\DefinitionResolver');
        $this->resolver = new DecoratorResolver($container, $this->parentResolver);
    }

    /**
     * @test
     */
    public function should_resolve_decorators()
    {
        $previousDefinition = new ValueDefinition('foo', 'bar');

        $callable = function ($previous, ContainerInterface $container) {
            return $previous . 'baz';
        };
        $definition = new DecoratorDefinition('foo', $callable);
        $definition->setSubDefinition($previousDefinition);

        $this->parentResolver->expects($this->once())
            ->method('resolve')
            ->with($previousDefinition)
            ->will($this->returnValue($previousDefinition->getValue()));

        $value = $this->resolver->resolve($definition);

        $this->assertEquals('barbaz', $value);
    }

    /**
     * @test
     * @expectedException \DI\Definition\Exception\DefinitionException
     * @expectedExceptionMessage The decorator "foo" is not callable
     */
    public function should_throw_if_the_factory_is_not_callable()
    {
        $definition = new DecoratorDefinition('foo', 'Hello world');

        $this->resolver->resolve($definition);
    }
}
