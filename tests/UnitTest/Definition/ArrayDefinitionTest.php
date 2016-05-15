<?php

namespace DI\Test\UnitTest\Definition;

use DI\Definition\ArrayDefinition;
use DI\Definition\CacheableDefinition;

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
    public function should_be_cacheable()
    {
        $this->assertNotInstanceOf(CacheableDefinition::class, new ArrayDefinition('foo', []));
    }

    /**
     * @test
     */
    public function should_cast_to_string()
    {
        $definition = new ArrayDefinition('foo', [
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
        $this->assertEquals($str, (string) new ArrayDefinition('foo', ['test' => 'hello']));
    }

    /**
     * @test
     */
    public function should_cast_to_string_with_nested_definitions()
    {
        $definition = new ArrayDefinition('foo', [
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
