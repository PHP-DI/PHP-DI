<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace UnitTests\DI;

use DI\Debug;

/**
 * Tests the helper functions.
 *
 * @covers \DI\Debug
 */
class DebugTest extends \PHPUnit_Framework_TestCase
{
    public function testDumpDefinition()
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
