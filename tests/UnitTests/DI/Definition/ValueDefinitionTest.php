<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.io/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace UnitTests\DI\Definition;

use DI\Definition\ValueDefinition;
use DI\Scope;

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

    public function testCacheable()
    {
        $definition = new ValueDefinition('foo', 1);

        $this->assertFalse($definition->isCacheable());
    }

    public function testMergeable()
    {
        $this->assertFalse(ValueDefinition::isMergeable());
    }

    public function testScope()
    {
        $definition = new ValueDefinition('foo', 1);

        $this->assertEquals(Scope::SINGLETON(), $definition->getScope());
    }

    /**
     * @expectedException \BadMethodCallException
     */
    public function testMerge()
    {
        $definition1 = new ValueDefinition('foo', 1);
        $definition2 = new ValueDefinition('foo', 2);
        $definition1->merge($definition2);
    }
}
