<?php

namespace DI\Test\UnitTest\Definition\Resolver;

use DI\Definition\Resolver\ValueResolver;
use DI\Definition\ValueDefinition;

/**
 * @covers \DI\Definition\Resolver\ValueResolver
 */
class ValueResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function should_resolve_value_definitions()
    {
        $resolver = new ValueResolver();

        $definition = new ValueDefinition('foo', 'bar');

        $this->assertTrue($resolver->isResolvable($definition));
        $this->assertEquals('bar', $resolver->resolve($definition));
    }
}
