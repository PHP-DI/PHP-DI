<?php

namespace DI\Test\UnitTest\Definition;

use DI\Definition\AliasDefinition;
use DI\Definition\EntryReference;

/**
 * @covers \DI\Definition\EntryReference
 */
class EntryReferenceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function should_have_a_name()
    {
        $helper = new EntryReference('foo');

        $this->assertSame('foo', $helper->getName());
    }

    /**
     * @test
     */
    public function should_produce_a_definition()
    {
        $helper = new EntryReference('bar');
        $definition = $helper->getDefinition('foo');

        $this->assertTrue($definition instanceof AliasDefinition);
        $this->assertSame('foo', $definition->getName());
        $this->assertSame('bar', $definition->getTarget());
    }
}
