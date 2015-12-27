<?php

namespace DI\Test\UnitTest\Definition\Resolver;

use DI\Definition\Resolver\SelfResolver;
use DI\Definition\ValueDefinition;
use EasyMock\EasyMock;

/**
 * @covers \DI\Definition\Resolver\SelfResolver
 */
class SelfResolverTest extends \PHPUnit_Framework_TestCase
{
    use EasyMock;

    /**
     * @test
     */
    public function should_resolve_definitions()
    {
        $container = $this->easyMock('Interop\Container\ContainerInterface');
        $resolver = new SelfResolver($container);

        $definition = new ValueDefinition('foo', 'bar');

        $this->assertTrue($resolver->isResolvable($definition));
        $this->assertEquals('bar', $resolver->resolve($definition));
    }
}
