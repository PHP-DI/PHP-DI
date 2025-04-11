<?php

declare(strict_types=1);

namespace DI\Test\UnitTest\Definition\Resolver;

use DI\Definition\Reference;
use DI\Definition\ArrayDefinition;
use DI\Definition\ObjectDefinition;
use DI\Definition\Resolver\ArrayResolver;
use DI\Definition\Resolver\DefinitionResolver;
use EasyMock\EasyMock;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use DI\DependencyException;

/**
 * @covers \DI\Definition\Resolver\ArrayResolver
 */
class ArrayResolverTest extends TestCase
{
    use EasyMock;

    private MockObject|DefinitionResolver $parentResolver;

    private ArrayResolver $resolver;

    public function setUp(): void
    {
        $this->parentResolver = $this->easyMock(DefinitionResolver::class);
        $this->resolver = new ArrayResolver($this->parentResolver);
    }

    /**
     * @test
     */
    public function should_resolve_array_of_values()
    {
        $definition = new ArrayDefinition([
            'bar',
            42,
        ]);

        $value = $this->resolver->resolve($definition);

        $this->assertEquals(['bar', 42], $value);
    }

    /**
     * @test
     */
    public function should_resolve_nested_definitions()
    {
        $this->parentResolver->expects($this->exactly(2))
            ->method('resolve')
            ->willReturnMap([
                [$this->isInstanceOf(Reference::class)],
                [$this->isInstanceOf(ObjectDefinition::class)],
            ])
            ->willReturnOnConsecutiveCalls(42, new \stdClass());

        $definition = new ArrayDefinition([
            'bar',
            new Reference('bar'),
            new ObjectDefinition('', 'bar'),
        ]);

        $value = $this->resolver->resolve($definition);

        $this->assertEquals(['bar', 42, new \stdClass()], $value);
    }

    /**
     * @test
     */
    public function resolve_should_preserve_keys()
    {
        $definition = new ArrayDefinition([
            'hello' => 'world',
        ]);

        $value = $this->resolver->resolve($definition);

        $this->assertEquals(['hello' => 'world'], $value);
    }

    /**
     * @test
     */
    public function should_throw_with_a_nice_message()
    {
        $this->expectException(DependencyException::class);
        $this->expectExceptionMessage('Error while resolving foo[0]. This is a message');
        $this->parentResolver->expects($this->once())
            ->method('resolve')
            ->willThrowException(new \Exception('This is a message'));

        $definition = new ArrayDefinition([
            new Reference('bar'),
        ]);
        $definition->setName('foo');

        $this->resolver->resolve($definition);
    }
}
