<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.io/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace UnitTests\DI\Definition;

use DI\Definition\FunctionCallDefinition;

/**
 * Test class for FunctionCallDefinition
 *
 * @covers \DI\Definition\FunctionCallDefinition
 */
class FunctionCallDefinitionTest extends \PHPUnit_Framework_TestCase
{
    public function testCallable()
    {
        $definition = new FunctionCallDefinition('foo');
        $this->assertEquals('foo', $definition->getCallable());

        $closure = function () {
        };
        $definition = new FunctionCallDefinition($closure);
        $this->assertSame($closure, $definition->getCallable());
    }

    public function testGetReflection()
    {
        $closure = function () {
            return 42;
        };
        $definition = new FunctionCallDefinition($closure);

        $reflectionClosure = $definition->getReflection()->getClosure();
        $this->assertEquals($closure(), $reflectionClosure());
    }
}
