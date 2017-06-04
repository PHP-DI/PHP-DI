<?php

declare(strict_types=1);

namespace DI\Test\UnitTest\Definition;

use DI\Definition\ArrayDefinition;
use DI\Definition\ArrayDefinitionExtension;
use DI\Definition\ValueDefinition;

/**
 * @covers \DI\Definition\ArrayDefinitionExtension
 */
class ArrayDefinitionExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function test_getters()
    {
        $definition = new ArrayDefinitionExtension('foo', ['hello']);

        $this->assertEquals('foo', $definition->getName());
        $this->assertEquals('foo', $definition->getSubDefinitionName());
        $this->assertEquals(['hello'], $definition->getValues());
    }

    /**
     * @test
     */
    public function should_append_values_after_sub_definitions_values()
    {
        $definition = new ArrayDefinitionExtension('name', ['foo']);
        $definition->setSubDefinition(new ArrayDefinition('name', ['bar']));

        $expected = [
            'bar',
            'foo',
        ];

        $this->assertEquals($expected, $definition->getValues());
    }

    /**
     * @test
     * @expectedException \DI\Definition\Exception\InvalidDefinition
     * @expectedExceptionMessage Definition name tries to add array entries but the previous definition is not an array
     */
    public function should_error_if_not_extending_an_array()
    {
        $definition = new ArrayDefinitionExtension('name', ['foo']);
        $definition->setSubDefinition(new ValueDefinition('name', 'value'));
    }
}
