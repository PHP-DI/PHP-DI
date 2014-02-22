<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace UnitTests\DI\Definition\Dumper;

use DI\Definition\Dumper\ValueDefinitionDumper;
use DI\Definition\FactoryDefinition;
use DI\Definition\ValueDefinition;

/**
 * @covers \DI\Definition\Dumper\ValueDefinitionDumper
 */
class ValueDefinitionDumperTest extends \PHPUnit_Framework_TestCase
{
    public function testStringValue()
    {
        $definition = new ValueDefinition('foo', 'bar');
        $dumper = new ValueDefinitionDumper();

        $str = 'Value (
    string(3) "bar"
)';

        $this->assertEquals($str, $dumper->dump($definition));
    }

    public function testIntValue()
    {
        $definition = new ValueDefinition('foo', 3306);
        $dumper = new ValueDefinitionDumper();

        $str = 'Value (
    int(3306)
)';

        $this->assertEquals($str, $dumper->dump($definition));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage This definition dumper is only compatible with ValueDefinition objects, DI\Definition\FactoryDefinition given
     */
    public function testInvalidDefinitionType()
    {
        $definition = new FactoryDefinition('foo', function () {
        });
        $dumper = new ValueDefinitionDumper();

        $dumper->dump($definition);
    }
}
