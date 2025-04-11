<?php

declare(strict_types=1);

namespace DI\Test\UnitTest\Definition;

use DI\Definition\ArrayDefinition;
use PHPUnit\Framework\TestCase;

/**
 * @covers \DI\Definition\ArrayDefinition
 */
class ArrayDefinitionTest extends TestCase
{
    /**
     * @test
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function should_contain_values()
    {
        $definition = new ArrayDefinition(['bar']);
        $definition->setName('foo');

        $this->assertEquals('foo', $definition->getName());
        $this->assertEquals(['bar'], $definition->getValues());
    }

    /**
     * @test
     */
    #[\PHPUnit\Framework\Attributes\Test]
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
    #[\PHPUnit\Framework\Attributes\Test]
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
    #[\PHPUnit\Framework\Attributes\Test]
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
