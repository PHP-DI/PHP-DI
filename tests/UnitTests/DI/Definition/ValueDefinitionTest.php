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
 *
 * @covers \DI\Definition\ValueDefinition
 */
class ValueDefinitionTest extends \PHPUnit_Framework_TestCase
{
    public function testGetters()
    {
        $definition = new ValueDefinition('foo', 1);

        $this->assertEquals('foo', $definition->getName());
        $this->assertEquals(1, $definition->getValue());
    }

    public function testScope()
    {
        $definition = new ValueDefinition('foo', 1);

        $this->assertEquals(Scope::SINGLETON(), $definition->getScope());
    }

    public function testCacheable()
    {
        $this->assertNotInstanceOf('DI\Definition\CacheableDefinition', new ValueDefinition('foo', 'bar'));
    }
}
