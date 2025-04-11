<?php

declare(strict_types=1);

namespace DI\Test\UnitTest\Definition;

use DI\Definition\StringDefinition;
use DI\NotFoundException;
use EasyMock\EasyMock;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use DI\DependencyException;

/**
 * @covers \DI\Definition\StringDefinition
 */
class StringDefinitionTest extends TestCase
{
    use EasyMock;

    public function test_getters()
    {
        $definition = new StringDefinition('bar');
        $definition->setName('foo');

        $this->assertEquals('foo', $definition->getName());
        $this->assertEquals('bar', $definition->getExpression());
    }

    /**
     * @test
     */
    public function should_be_resolvable()
    {
        $container = $this->easyMock(ContainerInterface::class);

        $definition = new StringDefinition('foo');

        $this->assertTrue($definition->isResolvable($container));
    }

    /**
     * @test
     */
    public function should_resolve_bare_strings()
    {
        $container = $this->easyMock(ContainerInterface::class);

        $definition = new StringDefinition('bar');

        $this->assertEquals('bar', $definition->resolve($container));
    }

    /**
     * @test
     */
    public function should_resolve_references()
    {
        $container = $this->easyMock(ContainerInterface::class, [
            'get' => 'bar',
        ]);

        $definition = new StringDefinition('{test}');

        $this->assertEquals('bar', $definition->resolve($container));
    }

    /**
     * @test
     */
    public function should_resolve_multiple_references()
    {
        $container = $this->easySpy(ContainerInterface::class);
        $container->expects($this->exactly(2))
            ->method('get')
            ->willReturnMap([
                ['tmp'],
                ['logs'],
            ])
            ->willReturnOnConsecutiveCalls('/private/tmp', 'myapp-logs');

        $definition = new StringDefinition('{tmp}/{logs}/app.log');

        $value = $definition->resolve($container);

        $this->assertEquals('/private/tmp/myapp-logs/app.log', $value);
    }

    /**
     * @test
     */
    public function should_throw_on_unknown_entry_name()
    {
        $this->expectException(DependencyException::class);
        $this->expectExceptionMessage('Error while parsing string expression for entry \'foo\': No entry or class found for \'test\'');
        $container = $this->easyMock(ContainerInterface::class, [
            'get' => new NotFoundException("No entry or class found for 'test'"),
        ]);

        $definition = new StringDefinition('{test}');
        $definition->setName('foo');
        $definition->resolve($container);
    }

    /**
     * @test
     */
    public function should_cast_to_string()
    {
        $this->assertEquals('foo/{bar}', (string) new StringDefinition('foo/{bar}'));
    }
}
