<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.io/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace UnitTests\DI\Definition;

use DI\Definition\AliasDefinition;
use DI\Scope;

/**
 * Test class for AliasDefinition
 *
 * @covers \DI\Definition\AliasDefinition
 */
class AliasDefinitionTest extends \PHPUnit_Framework_TestCase
{
    public function testGetters()
    {
        $definition = new AliasDefinition('foo', 'bar');

        $this->assertEquals('foo', $definition->getName());
        $this->assertEquals('bar', $definition->getTargetEntryName());
    }

    public function testScope()
    {
        $definition = new AliasDefinition('foo', 'bar');

        $this->assertEquals(Scope::PROTOTYPE(), $definition->getScope());
    }

    public function testCacheable()
    {
        $definition = new AliasDefinition('foo', 'bar');
        $this->assertTrue($definition->isCacheable());
    }
}
