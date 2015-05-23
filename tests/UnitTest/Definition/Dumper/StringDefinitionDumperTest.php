<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Test\UnitTest\Definition\Dumper;

use DI\Definition\Dumper\StringDefinitionDumper;
use DI\Definition\StringDefinition;
use DI\Definition\ValueDefinition;

/**
 * @covers \DI\Definition\Dumper\StringDefinitionDumper
 */
class StringDefinitionDumperTest extends \PHPUnit_Framework_TestCase
{
    public function testDump()
    {
        $dumper = new StringDefinitionDumper();

        $this->assertEquals('foo/{bar}', $dumper->dump(new StringDefinition('foo', 'foo/{bar}')));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage This definition dumper is only compatible with StringDefinition objects, DI\Definition\ValueDefinition given
     */
    public function testInvalidDefinitionType()
    {
        $definition = new ValueDefinition('foo', 'bar');
        $dumper = new StringDefinitionDumper();

        $dumper->dump($definition);
    }
}
