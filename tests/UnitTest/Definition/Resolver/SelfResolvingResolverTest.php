<?php

namespace DI\Test\UnitTest\Definition\Resolver;

use DI\Definition\Resolver\SelfResolvingResolver;
use DI\Definition\ValueDefinition;
use EasyMock\EasyMock;
use Interop\Container\ContainerInterface;

/**
 * @covers \DI\Definition\Resolver\SelfResolvingResolver
 */
class SelfResolvingResolverTest extends \PHPUnit_Framework_TestCase
{
    use EasyMock;

    /**
     * @test
     */
    public function should_resolve_definitions()
    {
        $container = $this->easyMock(ContainerInterface::class);
        $resolver = new SelfResolvingResolver($container);

        $definition = new ValueDefinition('foo', 'bar');

        $this->assertTrue($resolver->isResolvable($definition));
        $this->assertEquals('bar', $resolver->resolve($definition));
    }
}
