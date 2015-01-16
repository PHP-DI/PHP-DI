<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Test\UnitTest\Definition;

use DI\Definition\StringDefinition;
use DI\Scope;

/**
 * @covers \DI\Definition\StringDefinition
 */
class StringDefinitionTest extends \PHPUnit_Framework_TestCase
{
    public function testGetters()
    {
        $definition = new StringDefinition('foo', 'bar');

        $this->assertEquals('foo', $definition->getName());
        $this->assertEquals('bar', $definition->getExpression());
    }

    public function testScope()
    {
        $definition = new StringDefinition('foo', 'bar');

        $this->assertEquals(Scope::SINGLETON(), $definition->getScope());
    }

    public function testCacheable()
    {
        $this->assertNotInstanceOf('DI\Definition\CacheableDefinition', new StringDefinition('foo', 'bar'));
    }
}
