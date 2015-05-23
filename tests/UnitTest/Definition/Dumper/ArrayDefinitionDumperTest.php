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
    /**
     * @test
     */
    public function should_dump_array_definitions()
    {
        $definition = new ArrayDefinition('foo', [
            'hello',
            'world',
        ]);
        $dumper = new ArrayDefinitionDumper();

        $str = "[
    0 => 'hello',
    1 => 'world',
]";

        $this->assertEquals($str, $dumper->dump($definition));
    }

    /**
     * @test
     */
    public function should_dump_array_with_keys()
    {
        $definition = new ArrayDefinition('foo', [
            'test' => 'hello',
        ]);
        $dumper = new ArrayDefinitionDumper();

        $str = "[
    'test' => 'hello',
]";

        $this->assertEquals($str, $dumper->dump($definition));
    }

    /**
     * @test
     */
    public function should_dump_array_containing_nested_definitions()
    {
        $definition = new ArrayDefinition('foo', [
            \DI\get('foo'),
            \DI\env('foo'),
        ]);
        $dumper = new ArrayDefinitionDumper();

        $str = '[
    0 => get(foo),
    1 => Environment variable (
        variable = foo
        optional = no
    ),
]';

        $this->assertEquals($str, $dumper->dump($definition));
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage This definition dumper is only compatible with ArrayDefinition objects, DI\Definition\ValueDefinition given
     */
    public function should_only_accept_array_definitions()
    {
        $definition = new ValueDefinition('foo', 'bar');
        $dumper = new ArrayDefinitionDumper();

        $dumper->dump($definition);
    }
}
