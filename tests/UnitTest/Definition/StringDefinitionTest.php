<?php

namespace DI\Test\UnitTest\Definition;

use DI\Definition\StringDefinition;
use DI\NotFoundException;
use DI\Scope;
use EasyMock\EasyMock;

/**
 * @covers \DI\Definition\StringDefinition
 */
class StringDefinitionTest extends \PHPUnit_Framework_TestCase
{
    use EasyMock;

    public function test_getters()
    {
        $definition = new StringDefinition('foo', 'bar');

        $this->assertEquals('foo', $definition->getName());
        $this->assertEquals('bar', $definition->getExpression());
    }

    /**
     * @test
     */
    public function should_have_singleton_scope()
    {
        $definition = new StringDefinition('foo', 'bar');

        $this->assertEquals(Scope::SINGLETON, $definition->getScope());
    }

    /**
     * @test
     */
    public function should_not_be_cacheable()
    {
        $this->assertNotInstanceOf('DI\Definition\CacheableDefinition', new StringDefinition('foo', 'bar'));
    }

    /**
     * @test
     */
    public function should_be_resolvable()
    {
        $container = $this->easyMock('Interop\Container\ContainerInterface');

        $definition = new StringDefinition('foo', 'bar');

        $this->assertTrue($definition->isResolvable($container));
    }

    /**
     * @test
     */
    public function should_resolve_bare_strings()
    {
        $container = $this->easyMock('Interop\Container\ContainerInterface');

        $definition = new StringDefinition('foo', 'bar');

        $this->assertEquals('bar', $definition->resolve($container));
    }

    /**
     * @test
     */
    public function should_resolve_references()
    {
        $container = $this->easyMock('Interop\Container\ContainerInterface', [
            'get' => 'bar',
        ]);

        $definition = new StringDefinition('foo', '{test}');

        $this->assertEquals('bar', $definition->resolve($container));
    }

    /**
     * @test
     */
    public function should_resolve_multiple_references()
    {
        $container = $this->easySpy('Interop\Container\ContainerInterface');
        $container->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive(['tmp'], ['logs'])
            ->willReturnOnConsecutiveCalls('/private/tmp', 'myapp-logs');

        $definition = new StringDefinition('foo', '{tmp}/{logs}/app.log');

        $value = $definition->resolve($container);

        $this->assertEquals('/private/tmp/myapp-logs/app.log', $value);
    }

    /**
     * @test
     * @expectedException \DI\DependencyException
     * @expectedExceptionMessage Error while parsing string expression for entry 'foo': No entry or class found for 'test'
     */
    public function should_throw_on_unknown_entry_name()
    {
        $container = $this->easyMock('Interop\Container\ContainerInterface', [
            'get' => new NotFoundException("No entry or class found for 'test'"),
        ]);

        $definition = new StringDefinition('foo', '{test}');
        $definition->resolve($container);
    }
}
