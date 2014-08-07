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
use DI\Definition\ClassDefinition;
use DI\Definition\Dumper\AliasDefinitionDumper;
use DI\Definition\Dumper\ClassDefinitionDumper;
use DI\Definition\Dumper\DefinitionDumperDispatcher;
use DI\Definition\Dumper\FactoryDefinitionDumper;
use DI\Definition\Dumper\ValueDefinitionDumper;
use DI\Definition\Dumper\EnvironmentVariableDefinitionDumper;
use DI\Definition\FactoryDefinition;
use DI\Definition\ValueDefinition;
use DI\Definition\EnvironmentVariableDefinition;

/**
 * @covers \DI\Definition\Dumper\DefinitionDumperDispatcher
 */
class DefinitionDumperDispatcherTest extends \PHPUnit_Framework_TestCase
{
    public function testRegisterDumper()
    {
        $definition = new ValueDefinition('foo', 'bar');

        $subDumper = $this->getMockForAbstractClass('DI\Definition\Dumper\DefinitionDumper');
        // Check that the sub-dumper is really called
        $subDumper->expects($this->once())
            ->method('dump')
            ->with($definition)
            ->will($this->returnValue('foo'));

        $dumper = new DefinitionDumperDispatcher();
        $dumper->registerDumper(get_class($definition), $subDumper);

        $this->assertEquals('foo', $dumper->dump($definition));
    }

    public function testDumpValueDefinition()
    {
        $dumper = new DefinitionDumperDispatcher();
        $dumper->registerDefaultDumpers();

        $definition = new ValueDefinition('foo', 'bar');

        $valueDumper = new ValueDefinitionDumper();
        $this->assertEquals($valueDumper->dump($definition), $dumper->dump($definition));
    }

    public function testDumpAliasDefinition()
    {
        $dumper = new DefinitionDumperDispatcher();
        $dumper->registerDefaultDumpers();

        $definition = new AliasDefinition('foo', 'bar');

        $aliasDumper = new AliasDefinitionDumper();
        $this->assertEquals($aliasDumper->dump($definition), $dumper->dump($definition));
    }

    public function testDumpFactoryDefinition()
    {
        $dumper = new DefinitionDumperDispatcher();
        $dumper->registerDefaultDumpers();

        $definition = new FactoryDefinition('foo', 'strlen');

        $factoryDumper = new FactoryDefinitionDumper();
        $this->assertEquals($factoryDumper->dump($definition), $dumper->dump($definition));
    }

    public function testDumpClassDefinition()
    {
        $dumper = new DefinitionDumperDispatcher();
        $dumper->registerDefaultDumpers();

        $definition = new ClassDefinition('foo', 'MyClass');

        $classDumper = new ClassDefinitionDumper();
        $this->assertEquals($classDumper->dump($definition), $dumper->dump($definition));
    }

    public function testDumpEnvironmentVariableDefinition()
    {
        $dumper = new DefinitionDumperDispatcher();
        $dumper->registerDefaultDumpers();

        $definition = new EnvironmentVariableDefinition('foo', 'bar');

        $classDumper = new EnvironmentVariableDefinitionDumper();
        $this->assertEquals($classDumper->dump($definition), $dumper->dump($definition));
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage There is no DefinitionDumper capable of dumping this definition of type DI\Definition\ValueDefinition
     */
    public function testInvalidDefinitionType()
    {
        $dumper = new DefinitionDumperDispatcher(false);
        $dumper->dump(new ValueDefinition('foo', 'bar'));
    }
}
