<?php

declare(strict_types=1);

namespace DI\Test\UnitTest\Definition\Resolver;

use DI\Definition\FactoryDefinition;
use DI\Definition\ObjectDefinition;
use DI\Definition\Resolver\DefinitionResolver;
use DI\Definition\Resolver\FactoryResolver;
use DI\NotFoundException;
use DI\Test\UnitTest\Definition\Resolver\Fixture\FixtureClass;
use DI\Test\UnitTest\Definition\Resolver\Fixture\NoConstructor;
use EasyMock\EasyMock;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use DI\Definition\Exception\InvalidDefinition;

/**
 * @covers \DI\Definition\Resolver\FactoryResolver
 */
class FactoryResolverTest extends TestCase
{
    use EasyMock;

    /**
     * @test
     */
    public function should_resolve_callables()
    {
        $container = $this->easyMock(ContainerInterface::class);
        $resolver = new FactoryResolver($container, $this->easyMock(DefinitionResolver::class));

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
        $resolver = new FactoryResolver($container, $this->easyMock(DefinitionResolver::class));

        $definition = new FactoryDefinition('foo', function ($c) {
            return $c;
        });

        $value = $resolver->resolve($definition);

        $this->assertInstanceOf(ContainerInterface::class, $value);
    }

    /**
     * @test
     */
    public function should_throw_if_the_factory_is_not_callable()
    {
        $this->expectException(InvalidDefinition::class);
        $this->expectExceptionMessage('Entry "foo" cannot be resolved: factory \'Hello world\' is neither a callable nor a valid container entry');
        $container = $this->easyMock(ContainerInterface::class);
        $resolver = new FactoryResolver($container, $this->easyMock(DefinitionResolver::class));

        $definition = new FactoryDefinition('foo', 'Hello world');

        $container->method('get')
            ->willThrowException(new NotFoundException);

        $resolver->resolve($definition);
    }

    /**
     * @test
     */
    public function should_throw_if_not_enough_parameters()
    {
        $this->expectException(InvalidDefinition::class);
        $this->expectExceptionMessage('Entry "foo" cannot be resolved: Unable to invoke the callable because no value was given for parameter 3 ($c)');
        $container = $this->easyMock(ContainerInterface::class);
        $resolver = new FactoryResolver($container, $this->easyMock(DefinitionResolver::class));

        $definition = new FactoryDefinition('foo', function ($a, $b, $c) {
        });

        $resolver->resolve($definition);
    }

    /**
     * @test
     */
    public function should_inject_parameters()
    {
        $container = $this->easyMock(ContainerInterface::class);
        $resolver = new FactoryResolver($container, $this->easyMock(DefinitionResolver::class));

        $testCase = $this;
        $definition = new FactoryDefinition('foo', function ($c, $par1, $par2, $baz) use ($testCase) {
            $testCase->assertEquals('Parameter 1', $par1);
            $testCase->assertEquals(2, $par2);
            $testCase->assertEquals('bar', $baz);

            return $c;
        }, ['par1' => 'Parameter 1', 'par2' => 2]);

        $value = $resolver->resolve($definition, ['baz' => 'bar']);

        $this->assertInstanceOf(ContainerInterface::class, $value);
    }

    /**
     * @test
     */
    public function should_resolve_nested_definition_in_parameters()
    {
        $container = $this->easyMock(ContainerInterface::class);
        $parentResolver = $this->easyMock(DefinitionResolver::class);
        $resolver = new FactoryResolver($container, $parentResolver);

        $definition = new FactoryDefinition('foo', function ($par1) {
            return new FixtureClass($par1);
        }, ['par1' => new ObjectDefinition('', NoConstructor::class)]);

        $parentResolver->expects($this->once())
            ->method('resolve')
            ->with($this->isInstanceOf(ObjectDefinition::class))
            ->willReturn('bar');

        $object = $resolver->resolve($definition);

        $this->assertInstanceOf(FixtureClass::class, $object);
        $this->assertEquals('bar', $object->constructorParam1);
    }
}
