<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.io/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace UnitTests\DI\Definition;

use DI\Container;
use DI\Definition\ClassDefinition;
use DI\Definition\ClosureDefinition;

/**
 * Test class for ClosureDefinition
 */
class ClosureDefinitionTest extends \PHPUnit_Framework_TestCase
{

    public function testGetters()
    {
        $definition = new ClosureDefinition('foo', function() {
            return 1;
        });

        $this->assertEquals('foo', $definition->getName());

        $container = $this->getMockBuilder('DI\Container')
            ->disableOriginalConstructor()
            ->getMock();
        $this->assertEquals(1, $definition->getValue($container));
    }

    public function testGetValueWithContainer()
    {
        $definition = new ClosureDefinition('foo', function(Container $c) {
            return $c->get('bar');
        });

        $this->assertEquals('foo', $definition->getName());

        $container = $this->getMockBuilder('DI\Container')
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects($this->once())->method('get')->will($this->returnValue(1));
        $this->assertEquals(1, $definition->getValue($container));
    }

    public function testMergeable()
    {
        $this->assertFalse(ClosureDefinition::isMergeable());
    }

    /**
     * @expectedException \BadMethodCallException
     */
    public function testMerge()
    {
        $definition1 = new ClosureDefinition('foo', function() {
            return 1;
        });
        $definition2 = new ClosureDefinition('foo', function() {
            return 2;
        });
        $definition1->merge($definition2);
    }

}
