<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace UnitTests\DI\Definition\Dumper;

use DI\Definition\Dumper\EnvironmentVariableDefinitionDumper;
use DI\Definition\EntryReference;
use DI\Definition\EnvironmentVariableDefinition;
use DI\Definition\FactoryDefinition;

/**
 * @covers \DI\Definition\Dumper\EnvironmentVariableDefinitionDumper
 */
class EnvironmentVariableDefinitionDumperTest extends \PHPUnit_Framework_TestCase
{
    private $dumper;

    public function setUp()
    {
        $this->dumper = new EnvironmentVariableDefinitionDumper();
    }

    public function testDump()
    {
        $str = 'EnvironmentVariable (
    variable = bar
    optional = no
)';

        $this->assertEquals(
            $str,
            $this->dumper->dump(
                new EnvironmentVariableDefinition('foo', 'bar')
            )
        );
    }

    public function testDumpOptional()
    {
        $str = 'EnvironmentVariable (
    variable = bar
    optional = yes
    default = \'<default>\'
)';

        $this->assertEquals(
            $str,
            $this->dumper->dump(
                new EnvironmentVariableDefinition('foo', 'bar', true, '<default>')
            )
        );
    }

    public function testDumpOptionalWithLinkedDefault()
    {
        $str = 'EnvironmentVariable (
    variable = bar
    optional = yes
    default = link(foo)
)';

        $this->assertEquals(
            $str,
            $this->dumper->dump(
                new EnvironmentVariableDefinition('foo', 'bar', true, new EntryReference('foo'))
            )
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage This definition dumper is only compatible with EnvironmentVariableDefinition objects, DI\Definition\FactoryDefinition given
     */
    public function testDumpWithInvalidDefinitionType()
    {
        $this->dumper->dump(
            new FactoryDefinition('foo', function () {})
        );
    }
}
