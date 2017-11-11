<?php

declare(strict_types=1);

namespace DI\Test\UnitTest\Definition\Source;

use DI\Definition\Reference;
use PHPUnit\Framework\TestCase;
use DI\Definition\ObjectDefinition;
use DI\Definition\ObjectDefinition\MethodInjection;
use DI\Definition\Source\ReflectionBasedAutowiring;
use DI\Test\UnitTest\Definition\Source\Fixtures\AutowiringFixture;
use DI\Test\UnitTest\Definition\Source\Fixtures\AutowiringFixtureChild;

/**
 * @covers \DI\Definition\Source\ReflectionBasedAutowiring
 */
class ReflectionBasedAutowiringTest extends TestCase
{
    public function testUnknownClass()
    {
        $source = new ReflectionBasedAutowiring();
        $this->assertNull($source->autowire('foo'));
    }

    public function testConstructor()
    {
        $definition = (new ReflectionBasedAutowiring)->autowire(AutowiringFixture::class);
        $this->assertInstanceOf(ObjectDefinition::class, $definition);

        $constructorInjection = $definition->getConstructorInjection();
        $this->assertInstanceOf(MethodInjection::class, $constructorInjection);

        $parameters = $constructorInjection->getParameters();
        $this->assertCount(1, $parameters);

        $param1 = $parameters[0];
        $this->assertEquals(new Reference(AutowiringFixture::class), $param1);
    }

    public function testConstructorInParentClass()
    {
        $definition = (new ReflectionBasedAutowiring)->autowire(AutowiringFixtureChild::class);
        $this->assertInstanceOf(ObjectDefinition::class, $definition);

        $constructorInjection = $definition->getConstructorInjection();
        $this->assertInstanceOf(MethodInjection::class, $constructorInjection);

        $parameters = $constructorInjection->getParameters();
        $this->assertCount(1, $parameters);

        $param1 = $parameters[0];
        $this->assertEquals(new Reference(AutowiringFixture::class), $param1);
    }
}
