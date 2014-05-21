<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace UnitTests\DI\Definition\Dumper;

use DI\Definition\Dumper\FunctionCallDefinitionDumper;
use DI\Definition\FunctionCallDefinition;

/**
 * @covers \DI\Definition\Dumper\FunctionCallDefinitionDumper
 */
class FunctionCallDefinitionDumperTest extends \PHPUnit_Framework_TestCase
{
    public function testDump()
    {
        $definition = new FunctionCallDefinition(function ($logger, $default = 'foo') {
        });
        $dumper = new FunctionCallDefinitionDumper();

        $str = 'closure defined in ' . __FILE__ . ' at line 22(
    $logger = #UNDEFINED#
    $default = (default value) \'foo\'
)';

        $this->assertEquals($str, $dumper->dump($definition));
    }
}
