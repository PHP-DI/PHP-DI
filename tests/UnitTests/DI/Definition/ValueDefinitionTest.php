<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.com/PHP-DI/
 * @copyright 2012 Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace UnitTests\DI\Definition;

use DI\Definition\ClassDefinition;
use DI\Definition\ValueDefinition;

/**
 * Test class for ValueDefinition
 */
class ValueDefinitionTest extends \PHPUnit_Framework_TestCase
{

    public function testGetters()
    {
        $definition = new ValueDefinition('foo', 1);

        $this->assertEquals('foo', $definition->getName());
        $this->assertEquals(1, $definition->getValue());
    }

    /**
     * @expectedException \DI\Definition\DefinitionException
     * @expectedExceptionMessage DI definition conflict: there are 2 different definitions for 'foo' that are incompatible, they are not of the same type
     */
    public function testMergeIncompatibleTypes()
    {
        $definition = new ValueDefinition('foo', 1);
        $definition->merge(new ClassDefinition('foo', 'bar'));
    }

    public function testMerge1()
    {
        $definition1 = new ValueDefinition('foo', 1);
        $definition2 = new ValueDefinition('foo', 2);

        $definition1->merge($definition2);

        $this->assertEquals(2, $definition1->getValue());
    }

    public function testMerge2()
    {
        $definition1 = new ValueDefinition('foo', 2);
        $definition2 = new ValueDefinition('foo', 1);

        $definition1->merge($definition2);

        $this->assertEquals(1, $definition1->getValue());
    }

}
