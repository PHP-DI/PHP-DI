<?php

namespace DI\Test\UnitTest\Definition\Source;

use DI\Definition\EntryReference;
use DI\Definition\Source\Autowiring;

/**
 * @covers \DI\Definition\Source\Autowiring
 */
class AutowiringTest extends \PHPUnit_Framework_TestCase
{
    public function testUnknownClass()
    {
        $source = new Autowiring();
        $this->assertNull($source->getDefinition('foo'));
    }

    public function testConstructor()
    {
        $source = new Autowiring();
        $definition = $source->getDefinition('DI\Test\UnitTest\Definition\Source\Fixtures\AutowiringFixture');
        $this->assertInstanceOf('DI\Definition\ObjectDefinition', $definition);

        $constructorInjection = $definition->getConstructorInjection();
        $this->assertInstanceOf('DI\Definition\ObjectDefinition\MethodInjection', $constructorInjection);

        $parameters = $constructorInjection->getParameters();
        $this->assertCount(1, $parameters);

        $param1 = $parameters[0];
        $this->assertEquals(new EntryReference('DI\Test\UnitTest\Definition\Source\Fixtures\AutowiringFixture'), $param1);
    }

    public function testConstructorInParentClass()
    {
        $source = new Autowiring();
        $definition = $source->getDefinition('DI\Test\UnitTest\Definition\Source\Fixtures\AutowiringFixtureChild');
        $this->assertInstanceOf('DI\Definition\ObjectDefinition', $definition);

        $constructorInjection = $definition->getConstructorInjection();
        $this->assertInstanceOf('DI\Definition\ObjectDefinition\MethodInjection', $constructorInjection);

        $parameters = $constructorInjection->getParameters();
        $this->assertCount(1, $parameters);

        $param1 = $parameters[0];
        $this->assertEquals(new EntryReference('DI\Test\UnitTest\Definition\Source\Fixtures\AutowiringFixture'), $param1);
    }
}

class TestClass
{
    public function foo(\stdClass $foo, $bar)
    {
    }

    public function optional(\stdClass $foo = null)
    {
    }
}
