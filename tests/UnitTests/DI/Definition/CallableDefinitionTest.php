<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.io/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace UnitTests\DI\Definition;

use DI\Definition\CallableDefinition;

/**
 * Test class for CallableDefinition
 */
class CallableDefinitionTest extends \PHPUnit_Framework_TestCase
{
    public function testGetters()
    {
        $callable = function () {
            return 1;
        };
        $definition = new CallableDefinition('foo', $callable);

        $this->assertEquals('foo', $definition->getName());
        $this->assertEquals($callable, $definition->getCallable());
    }

    /**
     * Test that the definition accepts callable (not closures)
     */
    public function testAcceptArrayCallable()
    {
        $callable = array($this, 'foo');
        $definition = new CallableDefinition('foo', $callable);

        $this->assertEquals('foo', $definition->getName());
        $this->assertEquals($callable, $definition->getCallable());
    }

    public function testMergeable()
    {
        $this->assertFalse(CallableDefinition::isMergeable());
    }

    /**
     * @expectedException \BadMethodCallException
     */
    public function testMerge()
    {
        $definition1 = new CallableDefinition('foo', function () {
            return 1;
        });
        $definition2 = new CallableDefinition('foo', function () {
            return 2;
        });
        $definition1->merge($definition2);
    }
}
