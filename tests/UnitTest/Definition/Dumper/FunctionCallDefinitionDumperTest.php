<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Test\UnitTest\Definition\Dumper;

use DI\Definition\Dumper\FunctionCallDefinitionDumper;
use DI\Definition\FunctionCallDefinition;

/**
 * @covers \DI\Definition\Dumper\FunctionCallDefinitionDumper
 */
class FunctionCallDefinitionDumperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function dump_closure()
    {
        $definition = new FunctionCallDefinition(function ($undefined, $foo, $link, $default = 'foo') {
        });
        $definition->replaceParameters(array(
            1  => 'bar',
            2 => \DI\get('foo'),
        ));
        $dumper = new FunctionCallDefinitionDumper();

        $str = 'closure defined in ' . __FILE__ . ' at line 25(
    $undefined = #UNDEFINED#
    $foo = \'bar\'
    $link = get(foo)
    $default = (default value) \'foo\'
)';

        $this->assertEquals($str, $dumper->dump($definition));
    }

    /**
     * @test
     */
    public function dump_object_method()
    {
        $object = new \SplDoublyLinkedList;
        $definition = new FunctionCallDefinition(array($object, 'push'));
        $dumper = new FunctionCallDefinitionDumper();

        $str = 'SplDoublyLinkedList::push(
    $value = #UNDEFINED#
)';

        $this->assertEquals($str, $dumper->dump($definition));
    }

    /**
     * @test
     */
    public function dump_class_method()
    {
        $definition = new FunctionCallDefinition(array('SplDoublyLinkedList', 'push'));
        $dumper = new FunctionCallDefinitionDumper();

        $str = 'SplDoublyLinkedList::push(
    $value = #UNDEFINED#
)';

        $this->assertEquals($str, $dumper->dump($definition));
    }

    /**
     * @test
     */
    public function dump_static_method()
    {
        $definition = new FunctionCallDefinition(array('DI\Test\UnitTest\Definition\Dumper\TestClass', 'bar'));
        $dumper = new FunctionCallDefinitionDumper();

        $str = 'DI\Test\UnitTest\Definition\Dumper\TestClass::bar(
    $value = #UNDEFINED#
)';

        $this->assertEquals($str, $dumper->dump($definition));
    }

    /**
     * @test
     */
    public function dump_callable_object()
    {
        $definition = new FunctionCallDefinition(new CallableTestClass());
        $dumper = new FunctionCallDefinitionDumper();

        $str = 'DI\Test\UnitTest\Definition\Dumper\CallableTestClass::__invoke(
    $value = #UNDEFINED#
)';

        $this->assertEquals($str, $dumper->dump($definition));
    }

    /**
     * @test
     */
    public function dump_callable_class()
    {
        $definition = new FunctionCallDefinition('DI\Test\UnitTest\Definition\Dumper\CallableTestClass');
        $dumper = new FunctionCallDefinitionDumper();

        $str = 'DI\Test\UnitTest\Definition\Dumper\CallableTestClass::__invoke(
    $value = #UNDEFINED#
)';

        $this->assertEquals($str, $dumper->dump($definition));
    }
}

class TestClass
{
    public static function bar($value)
    {
    }
}

class CallableTestClass
{
    public function __invoke($value)
    {
    }
}
