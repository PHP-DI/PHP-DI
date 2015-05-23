<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Test\UnitTest\Definition\Dumper;

use DI\Definition\AliasDefinition;
use DI\Definition\DecoratorDefinition;
use DI\Definition\Dumper\DecoratorDefinitionDumper;
use DI\Definition\ObjectDefinition;
use DI\Definition\Dumper\AliasDefinitionDumper;
use DI\Definition\Dumper\ObjectDefinitionDumper;
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
    /**
     * @test
     */
    public function should_register_definition_dumpers()
    {
        $definition = new ValueDefinition('foo', 'bar');

        $subDumper = $this->getMockForAbstractClass('DI\Definition\Dumper\DefinitionDumper');
        // Check that the sub-dumper is really called
        $subDumper->expects($this->once())
            ->method('dump')
            ->with($definition)
            ->will($this->returnValue('foo'));

        $dumper = new DefinitionDumperDispatcher([
            get_class($definition) => $subDumper
        ]);

        $this->assertEquals('foo', $dumper->dump($definition));
    }

    /**
     * @test
     */
    public function should_dump_value_definitions_by_default()
    {
        $dumper = new DefinitionDumperDispatcher();

        $definition = new ValueDefinition('foo', 'bar');

        $valueDumper = new ValueDefinitionDumper();
        $this->assertEquals($valueDumper->dump($definition), $dumper->dump($definition));
    }

    /**
     * @test
     */
    public function should_dump_alias_definitions_by_default()
    {
        $dumper = new DefinitionDumperDispatcher();

        $definition = new AliasDefinition('foo', 'bar');

        $aliasDumper = new AliasDefinitionDumper();
        $this->assertEquals($aliasDumper->dump($definition), $dumper->dump($definition));
    }

    /**
     * @test
     */
    public function should_dump_factory_definitions_by_default()
    {
        $dumper = new DefinitionDumperDispatcher();

        $definition = new FactoryDefinition('foo', 'strlen');

        $factoryDumper = new FactoryDefinitionDumper();
        $this->assertEquals($factoryDumper->dump($definition), $dumper->dump($definition));
    }

    /**
     * @test
     */
    public function should_dump_decorator_definitions_by_default()
    {
        $dumper = new DefinitionDumperDispatcher();

        $definition = new DecoratorDefinition('foo', 'strlen');

        $decoratorDumper = new DecoratorDefinitionDumper();
        $this->assertEquals($decoratorDumper->dump($definition), $dumper->dump($definition));
    }

    /**
     * @test
     */
    public function should_dump_class_definitions_by_default()
    {
        $dumper = new DefinitionDumperDispatcher();

        $definition = new ObjectDefinition('foo', 'MyClass');

        $classDumper = new ObjectDefinitionDumper();
        $this->assertEquals($classDumper->dump($definition), $dumper->dump($definition));
    }

    /**
     * @test
     */
    public function should_dump_env_variables_definitions_by_default()
    {
        $dumper = new DefinitionDumperDispatcher();

        $definition = new EnvironmentVariableDefinition('foo', 'bar');

        $classDumper = new EnvironmentVariableDefinitionDumper();
        $this->assertEquals($classDumper->dump($definition), $dumper->dump($definition));
    }

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessage There is no DefinitionDumper capable of dumping this definition of type DI\Definition\ValueDefinition
     */
    public function should_only_accept_definitions_it_can_dump()
    {
        $dumper = new DefinitionDumperDispatcher([]);
        $dumper->dump(new ValueDefinition('foo', 'bar'));
    }
}
