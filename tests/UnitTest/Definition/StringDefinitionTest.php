<?php

namespace DI\Test\UnitTest\Definition;

use DI\Definition\StringDefinition;
use DI\Scope;

/**
 * @covers \DI\Definition\StringDefinition
 */
class StringDefinitionTest extends \PHPUnit_Framework_TestCase
{
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
}
