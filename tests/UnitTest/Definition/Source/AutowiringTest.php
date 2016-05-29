<?php

namespace DI\Test\UnitTest\Definition\Source;

use DI\Definition\AliasDefinition;
use DI\Definition\ObjectDefinition;
use DI\Definition\ObjectDefinition\MethodInjection;
use DI\Definition\Source\Autowiring;
use DI\Test\UnitTest\Definition\Source\Fixtures\AutowiringFixture;
use DI\Test\UnitTest\Definition\Source\Fixtures\AutowiringFixtureChild;

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
        $definition = (new Autowiring)->getDefinition(AutowiringFixture::class);
        $this->assertInstanceOf(ObjectDefinition::class, $definition);

        $constructorInjection = $definition->getConstructorInjection();
        $this->assertInstanceOf(MethodInjection::class, $constructorInjection);

        $parameters = $constructorInjection->getParameters();
        $this->assertCount(1, $parameters);

        $param1 = $parameters[0];
        $this->assertEquals(new AliasDefinition(AutowiringFixture::class), $param1);
    }

    public function testConstructorInParentClass()
    {
        $definition = (new Autowiring)->getDefinition(AutowiringFixtureChild::class);
        $this->assertInstanceOf(ObjectDefinition::class, $definition);

        $constructorInjection = $definition->getConstructorInjection();
        $this->assertInstanceOf(MethodInjection::class, $constructorInjection);

        $parameters = $constructorInjection->getParameters();
        $this->assertCount(1, $parameters);

        $param1 = $parameters[0];
        $this->assertEquals(new AliasDefinition(AutowiringFixture::class), $param1);
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
