<?php

namespace DI\Test\UnitTest\Definition\ClassDefinition;

use DI\Definition\ClassDefinition\MethodInjection;

/**
 * @covers \DI\Definition\ClassDefinition\MethodInjection
 */
class MethodInjectionTest extends \PHPUnit_Framework_TestCase
{
    public function testMergeParameters()
    {
        $definition1 = new MethodInjection('foo', array(
            0 => 'a',
            1 => 'b',
        ));
        $definition2 = new MethodInjection('foo', array(
            1 => 'c',
            2 => 'd',
        ));

        $definition1->merge($definition2);

        $this->assertEquals(array('a', 'b', 'd'), $definition1->getParameters());
    }

    /**
     * Check that a merge will preserve "null" injections
     */
    public function testMergeParametersPreservesNull()
    {
        $definition1 = new MethodInjection('foo', array(
            0 => null,
        ));
        $definition2 = new MethodInjection('foo', array(
            0 => 'bar',
        ));

        $definition1->merge($definition2);

        $this->assertEquals(array(null), $definition1->getParameters());
    }
}
