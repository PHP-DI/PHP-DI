<?php

namespace DI\Test\UnitTest;

use DI\Debug;

/**
 * @covers \DI\Debug
 */
class DebugTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function should_dump_definitions()
    {
        $definition = \DI\object()->getDefinition('foo');
        $str = <<<END
Object (
    class = #UNKNOWN# foo
    scope = singleton
    lazy = false
)
END;
        $this->assertEquals($str, Debug::dumpDefinition($definition));
    }
}
