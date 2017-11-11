<?php

declare(strict_types=1);

namespace DI\Test\UnitTest\Definition\ObjectDefinition;

use DI\Definition\ObjectDefinition\PropertyInjection;
use PHPUnit\Framework\TestCase;

/**
 * @covers \DI\Definition\ObjectDefinition\PropertyInjection
 */
class PropertyInjectionTest extends TestCase
{
    public function testGetters()
    {
        $definition = new PropertyInjection('foo', 'bar');

        $this->assertEquals('foo', $definition->getPropertyName());
        $this->assertEquals('bar', $definition->getValue());
    }
}
