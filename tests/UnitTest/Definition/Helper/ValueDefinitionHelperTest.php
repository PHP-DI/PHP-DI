<?php

declare(strict_types=1);

namespace DI\Test\UnitTest\Definition\Helper;

use DI\Definition\Helper\ValueDefinitionHelper;
use DI\Definition\ValueDefinition;

/**
 * @covers \DI\Definition\Helper\ValueDefinitionHelper
 */
class ValueDefinitionHelperTest extends \PHPUnit_Framework_TestCase
{
    public function testGetDefinition()
    {
        $helper = new ValueDefinitionHelper('bar');
        $definition = $helper->getDefinition('foo');

        $this->assertTrue($definition instanceof ValueDefinition);
        $this->assertSame('foo', $definition->getName());
        $this->assertSame('bar', $definition->getValue());
    }
}
