<?php

namespace DI\Test\UnitTest\Definition;

use DI\Definition\ArrayDefinition;
use DI\Scope;

/**
 * @covers \DI\Definition\ArrayDefinition
 */
class ArrayDefinitionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function should_contain_values()
    {
        $definition = new ArrayDefinition('foo', ['bar']);

        $this->assertEquals('foo', $definition->getName());
        $this->assertEquals(['bar'], $definition->getValues());
    }

    /**
     * @test
     */
    public function should_have_singleton_scope()
    {
        $definition = new ArrayDefinition('foo', []);

        $this->assertEquals(Scope::SINGLETON, $definition->getScope());
    }

    /**
     * @test
     */
    public function should_be_cacheable()
    {
        $this->assertNotInstanceOf('DI\Definition\CacheableDefinition', new ArrayDefinition('foo', []));
    }
}
