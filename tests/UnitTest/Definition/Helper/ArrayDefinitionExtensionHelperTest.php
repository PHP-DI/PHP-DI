<?php

declare(strict_types=1);

namespace DI\Test\UnitTest\Definition\Helper;

use DI\Definition\ArrayDefinitionExtension;
use DI\Definition\Helper\ArrayDefinitionExtensionHelper;

/**
 * @covers \DI\Definition\Helper\ArrayDefinitionExtensionHelper
 */
class ArrayDefinitionExtensionHelperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function test_array_extension()
    {
        $helper = new ArrayDefinitionExtensionHelper([
            'hello',
        ]);

        $definition = $helper->getDefinition('foo');

        $this->assertTrue($definition instanceof ArrayDefinitionExtension);
        $this->assertSame('foo', $definition->getName());
        $this->assertEquals(['hello'], $definition->getValues());
    }
}
