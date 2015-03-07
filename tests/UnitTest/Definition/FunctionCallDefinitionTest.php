<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Test\UnitTest\Definition;

use DI\Definition\FunctionCallDefinition;

/**
 * @covers \DI\Definition\FunctionCallDefinition
 */
class FunctionCallDefinitionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function should_accept_any_callable()
    {
        $definition = new FunctionCallDefinition('foo');
        $this->assertEquals('foo', $definition->getCallable());

        $closure = function () {
        };
        $definition = new FunctionCallDefinition($closure);
        $this->assertSame($closure, $definition->getCallable());
    }
}
