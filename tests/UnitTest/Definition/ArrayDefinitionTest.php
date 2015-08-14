<?php

namespace DI\Test\UnitTest\Definition;

use DI\Definition\ArrayDefinition;
use DI\Definition\CacheableDefinition;
use DI\Scope;

/**
 * @covers \DI\Definition\ArrayDefinition
 */
class ArrayDefinitionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function contains_values()
    {
        $definition = new ArrayDefinition(['foo', 'bar']);

        $this->assertEquals(['foo', 'bar'], $definition->getValues());
    }

    /**
     * @test
     */
    public function has_name()
    {
        $definition = new ArrayDefinition([]);
        $definition->setName('foo');

        $this->assertEquals('foo', $definition->getName());
    }

    /**
     * @test
     */
    public function has_singleton_scope()
    {
        $definition = new ArrayDefinition([]);

        $this->assertEquals(Scope::SINGLETON, $definition->getScope());
    }

    /**
     * @test
     */
    public function is_not_cacheable()
    {
        $this->assertNotInstanceOf(CacheableDefinition::class, new ArrayDefinition([]));
    }

    /**
     * @test
     */
    public function should_cast_to_string()
    {
        $definition = new ArrayDefinition([
            'hello',
            'world',
        ]);
        $str = "[
    0 => 'hello',
    1 => 'world',
]";
        $this->assertEquals($str, (string) $definition);
    }

    /**
     * @test
     */
    public function should_cast_to_string_with_string_keys()
    {
        $str = "[
    'test' => 'hello',
]";
        $this->assertEquals($str, (string) new ArrayDefinition(['test' => 'hello']));
    }

    /**
     * @test
     */
    public function should_cast_to_string_with_nested_definitions()
    {
        $definition = new ArrayDefinition([
            \DI\get('foo'),
            \DI\env('foo'),
        ]);
        $str = '[
    0 => get(foo),
    1 => Environment variable (
        variable = foo
        optional = no
    ),
]';
        $this->assertEquals($str, (string) $definition);
    }
}
