<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Test\UnitTest\Definition\Dumper;

use DI\Definition\ArrayDefinition;
use DI\Definition\Dumper\ArrayDefinitionDumper;
use DI\Definition\ValueDefinition;

/**
 * @covers \DI\Definition\Dumper\ArrayDefinitionDumper
 */
class ArrayDefinitionDumperTest extends \PHPUnit_Framework_TestCase
{
    public function testDumpArray()
    {
        $definition = new ArrayDefinition('foo', array(
            'hello',
            \DI\link('foo'),
        ));
        $dumper = new ArrayDefinitionDumper();

        $str = '[
    0 => string(5) "hello",
    1 => link(foo),
]';

        $this->assertEquals($str, $dumper->dump($definition));
    }

    public function testDumpArrayWithKeys()
    {
        $definition = new ArrayDefinition('foo', array(
            'test' => 'hello',
        ));
        $dumper = new ArrayDefinitionDumper();

        $str = '[
    "test" => string(5) "hello",
]';

        $this->assertEquals($str, $dumper->dump($definition));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage This definition dumper is only compatible with ArrayDefinition objects, DI\Definition\ValueDefinition given
     */
    public function testInvalidDefinitionType()
    {
        $definition = new ValueDefinition('foo', 'bar');
        $dumper = new ArrayDefinitionDumper();

        $dumper->dump($definition);
    }
}
