<?php

declare(strict_types=1);

namespace DI\Test\UnitTest\Definition\Resolver;

use DI\Definition\DecoratorDefinition;
use DI\Definition\Resolver\DecoratorResolver;
use DI\Definition\Resolver\DefinitionResolver;
use DI\Definition\ValueDefinition;
use EasyMock\EasyMock;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * @covers \DI\Definition\Resolver\DecoratorResolver
 */
class DecoratorResolverTest extends TestCase
{
    use EasyMock;

    /**
     * @var DecoratorResolver
     */
    private $resolver;

    /**
     * @var DefinitionResolver|MockObject
     */
    private $parentResolver;

    public function setUp(): void
    {
        $container = $this->easyMock(ContainerInterface::class);
        $this->parentResolver = $this->easyMock(DefinitionResolver::class);
        $this->resolver = new DecoratorResolver($container, $this->parentResolver);
    }

    /**
     * @test
     */
    public function should_resolve_decorators()
    {
        $previousDefinition = new ValueDefinition('bar');

        $callable = function ($previous, ContainerInterface $container) {
            return $previous . 'baz';
        };
        $definition = new DecoratorDefinition('foo', $callable);
        $definition->setExtendedDefinition($previousDefinition);

        $this->parentResolver->expects($this->once())
            ->method('resolve')
            ->with($previousDefinition)
            ->willReturn($previousDefinition->getValue());

        $value = $this->resolver->resolve($definition);

        $this->assertEquals('barbaz', $value);
    }

    /**
     * @test
     */
    public function should_throw_if_the_factory_is_not_callable()
    {
        $this->expectException('DI\Definition\Exception\InvalidDefinition');
        $this->expectExceptionMessage('The decorator "foo" is not callable');
        $definition = new DecoratorDefinition('foo', 'Hello world');

        $this->resolver->resolve($definition);
    }
}
