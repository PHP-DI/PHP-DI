<?php

namespace DI\Test\UnitTest\Definition;

use DI\Definition\CacheableDefinition;
use DI\Definition\StringDefinition;
use DI\NotFoundException;
use DI\Scope;
use EasyMock\EasyMock;
use Interop\Container\ContainerInterface;

/**
 * @covers \DI\Definition\StringDefinition
 */
class StringDefinitionTest extends \PHPUnit_Framework_TestCase
{
    use EasyMock;

    /**
     * @test
     */
    public function has_expression()
    {
        $definition = new StringDefinition('foo');

        $this->assertEquals('foo', $definition->getExpression());
    }

    /**
     * @test
     */
    public function has_name()
    {
        $definition = new StringDefinition('aaa');
        $definition->setName('foo');

        $this->assertEquals('foo', $definition->getName());
    }

    /**
     * @test
     */
    public function has_singleton_scope()
    {
        $definition = new StringDefinition('foo');

        $this->assertEquals(Scope::SINGLETON, $definition->getScope());
    }

    /**
     * @test
     */
    public function is_not_cacheable()
    {
        $this->assertNotInstanceOf(CacheableDefinition::class, new StringDefinition('foo', 'bar'));
    }

    /**
     * @test
     */
    public function should_be_resolvable()
    {
        $container = $this->easyMock(ContainerInterface::class);

        $definition = new StringDefinition('foo', 'bar');

        $this->assertTrue($definition->isResolvable($container));
    }

    /**
     * @test
     */
    public function should_resolve_bare_strings()
    {
        $container = $this->easyMock(ContainerInterface::class);

        $definition = new StringDefinition('foo');

        $this->assertEquals('foo', $definition->resolve($container));
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
            ->withConsecutive(['tmp'], ['logs'])
            ->willReturnOnConsecutiveCalls('/private/tmp', 'myapp-logs');

        $definition = new StringDefinition('{tmp}/{logs}/app.log');

        $value = $definition->resolve($container);

        $this->assertEquals('/private/tmp/myapp-logs/app.log', $value);
    }

    /**
     * @test
     * @expectedException \DI\DependencyException
     * @expectedExceptionMessage Error while parsing string expression '{test}': No entry or class found for 'test'
     */
    public function should_throw_on_unknown_entry_name()
    {
        $container = $this->easyMock(ContainerInterface::class, [
            'get' => new NotFoundException("No entry or class found for 'test'"),
        ]);

        $definition = new StringDefinition('{test}');
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
