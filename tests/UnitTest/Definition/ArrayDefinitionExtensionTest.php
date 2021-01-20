<?php

declare(strict_types=1);

namespace DI\Test\UnitTest\Definition;

use DI\Definition\ArrayDefinition;
use DI\Definition\ArrayDefinitionExtension;
use DI\Definition\ValueDefinition;
use PHPUnit\Framework\TestCase;

/**
 * @covers \DI\Definition\ArrayDefinitionExtension
 */
class ArrayDefinitionExtensionTest extends TestCase
{
    /**
     * @test
     */
    public function test_getters()
    {
        $definition = new ArrayDefinitionExtension(['hello']);
        $definition->setName('foo');

        $this->assertEquals('foo', $definition->getName());
        $this->assertEquals(['hello'], $definition->getValues());
    }

    /**
     * @test
     */
    public function should_append_values_after_sub_definitions_values()
    {
        $definition = new ArrayDefinitionExtension(['foo']);
        $definition->setExtendedDefinition(new ArrayDefinition(['bar']));

        $expected = [
            'bar',
            'foo',
        ];

        $this->assertEquals($expected, $definition->getValues());
    }

    /**
     * @test
     */
    public function should_error_if_not_extending_an_array()
    {
        $this->expectException('DI\Definition\Exception\InvalidDefinition');
        $this->expectExceptionMessage('Definition name tries to add array entries but the previous definition is not an array');
        $definition = new ArrayDefinitionExtension(['foo']);
        $definition->setName('name');
        $definition->setExtendedDefinition(new ValueDefinition('value'));
    }
}
