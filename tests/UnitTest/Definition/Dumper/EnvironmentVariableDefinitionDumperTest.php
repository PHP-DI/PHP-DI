<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Test\UnitTest\Definition\Dumper;

use DI\Definition\Dumper\EnvironmentVariableDefinitionDumper;
use DI\Definition\EnvironmentVariableDefinition;
use DI\Definition\FactoryDefinition;

/**
 * @covers \DI\Definition\Dumper\EnvironmentVariableDefinitionDumper
 */
class EnvironmentVariableDefinitionDumperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EnvironmentVariableDefinitionDumper
     */
    private $dumper;

    public function setUp()
    {
        $this->dumper = new EnvironmentVariableDefinitionDumper();
    }

    /**
     * @test
     */
    public function should_dump_env_variable_definitions()
    {
        $str = 'Environment variable (
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

    /**
     * @test
     */
    public function should_dump_env_variable_definitions_with_default_value()
    {
        $str = 'Environment variable (
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

    /**
     * @test
     */
    public function should_dump_env_variable_definitions_with_reference_as_default_value()
    {
        $str = 'Environment variable (
    variable = bar
    optional = yes
    default = link(foo)
)';

        $this->assertEquals(
            $str,
            $this->dumper->dump(
                new EnvironmentVariableDefinition('foo', 'bar', true, \DI\link('foo'))
            )
        );
    }

    /**
     * @test
     */
    public function should_dump_env_variable_definitions_with_nested_definition_as_default_value()
    {
        $str = 'Environment variable (
    variable = bar
    optional = yes
    default = Environment variable (
        variable = foo
        optional = no
    )
)';

        $this->assertEquals(
            $str,
            $this->dumper->dump(
                new EnvironmentVariableDefinition('foo', 'bar', true, \DI\env('foo'))
            )
        );
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage This definition dumper is only compatible with EnvironmentVariableDefinition objects, DI\Definition\FactoryDefinition given
     */
    public function should_only_accept_env_variable_definitions()
    {
        $this->dumper->dump(
            new FactoryDefinition('foo', function () {})
        );
    }
}
