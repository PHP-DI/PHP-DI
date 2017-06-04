<?php

declare(strict_types=1);

namespace DI\Test\UnitTest\Definition\Helper;

use DI\Definition\Helper\StringDefinitionHelper;
use DI\Definition\StringDefinition;

/**
 * @covers \DI\Definition\Helper\StringDefinitionHelper
 */
class StringDefinitionHelperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function test_string_definition_helper()
    {
        $helper = new StringDefinitionHelper('hello');

        $definition = $helper->getDefinition('foo');

        $this->assertTrue($definition instanceof StringDefinition);
        $this->assertSame('foo', $definition->getName());
        $this->assertSame('hello', $definition->getExpression());
    }
}
