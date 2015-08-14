<?php

namespace DI\Test\UnitTest\Definition\ObjectDefinition;

use DI\Definition\CacheableDefinition;
use DI\Definition\ObjectDefinition\MethodInjection;
use DI\Scope;

/**
 * @covers \DI\Definition\ObjectDefinition\MethodInjection
 */
class MethodInjectionTest extends \PHPUnit_Framework_TestCase
{
    public function testBasicMethods()
    {
        $definition = new MethodInjection('foo');

        $this->assertEquals('foo', $definition->getMethodName());
        $this->assertEmpty($definition->getParameters());
    }

    public function testMergeParameters()
    {
        $definition1 = new MethodInjection('foo', [
            0 => 'a',
            1 => 'b',
        ]);
        $definition2 = new MethodInjection('foo', [
            1 => 'c',
            2 => 'd',
        ]);

        $definition1->merge($definition2);

        $this->assertEquals(['a', 'b', 'd'], $definition1->getParameters());
    }

    /**
     * Check that a merge will preserve "null" injections.
     */
    public function testMergeParametersPreservesNull()
    {
        $definition1 = new MethodInjection('foo', [
            0 => null,
        ]);
        $definition2 = new MethodInjection('foo', [
            0 => 'bar',
        ]);

        $definition1->merge($definition2);

        $this->assertEquals([null], $definition1->getParameters());
    }

    public function testNotCacheable()
    {
        $this->assertNotInstanceOf(CacheableDefinition::class, new MethodInjection('foo'));
    }

    public function testEmptyParameters()
    {
        $this->assertEmpty((new MethodInjection('foo'))->getParameters());
    }

    public function testGetParameters()
    {
        $definition = new MethodInjection('foo', ['bar']);

        $this->assertEquals(['bar'], $definition->getParameters());
    }

    public function testReplaceParameters()
    {
        $definition = new MethodInjection('foo', ['bar']);
        $definition->replaceParameters(['bim']);

        $this->assertEquals(['bim'], $definition->getParameters());
    }
}
