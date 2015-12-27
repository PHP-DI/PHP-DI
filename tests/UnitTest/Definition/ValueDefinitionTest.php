<?php

namespace DI\Test\UnitTest\Definition;

use DI\Definition\ValueDefinition;
use DI\Scope;
use EasyMock\EasyMock;

/**
 * @covers \DI\Definition\ValueDefinition
 */
class ValueDefinitionTest extends \PHPUnit_Framework_TestCase
{
    use EasyMock;

    public function test_getters()
    {
        $definition = new ValueDefinition('foo', 1);

        $this->assertEquals('foo', $definition->getName());
        $this->assertEquals(1, $definition->getValue());
    }

    /**
     * @test
     */
    public function should_have_singleton_scope()
    {
        $definition = new ValueDefinition('foo', 1);

        $this->assertEquals(Scope::SINGLETON, $definition->getScope());
    }

    /**
     * @test
     */
    public function should_not_be_cacheable()
    {
        $this->assertNotInstanceOf('DI\Definition\CacheableDefinition', new ValueDefinition('foo', 'bar'));
    }

    /**
     * @test
     */
    public function should_be_resolvable()
    {
        $definition = new ValueDefinition('foo', 'bar');
        $container = $this->easyMock('Interop\Container\ContainerInterface');
        $this->assertTrue($definition->isResolvable($container));
    }

    /**
     * @test
     */
    public function should_resolve()
    {
        $definition = new ValueDefinition('foo', 'bar');
        $container = $this->easyMock('Interop\Container\ContainerInterface');
        $this->assertEquals('bar', $definition->resolve($container));
    }
}
