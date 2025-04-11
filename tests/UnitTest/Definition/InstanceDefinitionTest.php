<?php

declare(strict_types=1);

namespace DI\Test\UnitTest\Definition;

use DI\Definition\InstanceDefinition;
use DI\Definition\ObjectDefinition;
use EasyMock\EasyMock;
use PHPUnit\Framework\TestCase;

/**
 * @covers \DI\Definition\InstanceDefinition
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\DI\Definition\InstanceDefinition::class)]
class InstanceDefinitionTest extends TestCase
{
    use EasyMock;

    /**
     * @test
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function should_contain_an_instance()
    {
        $instance = new \stdClass();

        $definition = new InstanceDefinition($instance, $this->easyMock(ObjectDefinition::class));

        $this->assertSame($instance, $definition->getInstance());
    }

    /**
     * @test
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function should_contain_an_object_definition()
    {
        $objectDefinition = $this->easyMock(ObjectDefinition::class);

        $definition = new InstanceDefinition(new \stdClass(), $objectDefinition);

        $this->assertSame($objectDefinition, $definition->getObjectDefinition());
    }
}
