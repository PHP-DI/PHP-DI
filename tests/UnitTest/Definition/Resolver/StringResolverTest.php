<?php

namespace DI\Test\UnitTest\Definition\Resolver;

use DI\Definition\Resolver\StringResolver;
use DI\Definition\StringDefinition;
use DI\NotFoundException;
use EasyMock\EasyMock;

/**
 * @covers \DI\Definition\Resolver\StringResolver
 */
class StringResolverTest extends \PHPUnit_Framework_TestCase
{
    use EasyMock;

    /**
     * @test
     */
    public function should_resolve_bare_strings()
    {
        $container = $this->easyMock('Interop\Container\ContainerInterface');
        $resolver = new StringResolver($container);

        $definition = new StringDefinition('foo', 'bar');

        $this->assertEquals('bar', $resolver->resolve($definition));
    }

    /**
     * @test
     */
    public function should_resolve_references()
    {
        $container = $this->easyMock('Interop\Container\ContainerInterface', [
            'get' => 'bar',
        ]);
        $resolver = new StringResolver($container);

        $definition = new StringDefinition('foo', '{test}');

        $this->assertEquals('bar', $resolver->resolve($definition));
    }

    /**
     * @test
     */
    public function should_resolve_multiple_references()
    {
        $container = $this->easyMock('Interop\Container\ContainerInterface');
        $container->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive(['tmp'], ['logs'])
            ->willReturnOnConsecutiveCalls('/private/tmp', 'myapp-logs');
        $resolver = new StringResolver($container);

        $definition = new StringDefinition('foo', '{tmp}/{logs}/app.log');

        $value = $resolver->resolve($definition);

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
        $resolver = new StringResolver($container);

        $resolver->resolve(new StringDefinition('foo', '{test}'));
    }
}
