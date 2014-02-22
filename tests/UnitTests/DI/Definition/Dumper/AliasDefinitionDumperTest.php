<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace UnitTests\DI\Definition\Dumper;

use DI\Definition\AliasDefinition;
use DI\Definition\Dumper\AliasDefinitionDumper;
use DI\Definition\ValueDefinition;

/**
 * @covers \DI\Definition\Dumper\AliasDefinitionDumper
 */
class AliasDefinitionDumperTest extends \PHPUnit_Framework_TestCase
{
    public function testDump()
    {
        $definition = new AliasDefinition('foo', 'bar');
        $dumper = new AliasDefinitionDumper();

        $str = 'Alias (
    foo => bar
)';

        $this->assertEquals($str, $dumper->dump($definition));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage This definition dumper is only compatible with AliasDefinition objects, DI\Definition\ValueDefinition given
     */
    public function testInvalidDefinitionType()
    {
        $definition = new ValueDefinition('foo', 'bar');
        $dumper = new AliasDefinitionDumper();

        $dumper->dump($definition);
    }
}
