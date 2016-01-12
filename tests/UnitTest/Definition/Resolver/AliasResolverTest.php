<?php

namespace DI\Test\UnitTest\Definition\Resolver;

use DI\Definition\AliasDefinition;
use DI\Definition\Resolver\AliasResolver;
use EasyMock\EasyMock;

/**
 * @covers \DI\Definition\Resolver\AliasResolver
 */
class AliasResolverTest extends \PHPUnit_Framework_TestCase
{
    use EasyMock;

    /**
     * @test
     */
    public function should_resolve_aliases()
    {
        $container = $this->easyMock('Interop\Container\ContainerInterface', [
            'get' => 42,
        ]);
        $resolver = new AliasResolver($container);

        $value = $resolver->resolve(new AliasDefinition('foo', 'bar'));

        $this->assertEquals(42, $value);
    }
}
