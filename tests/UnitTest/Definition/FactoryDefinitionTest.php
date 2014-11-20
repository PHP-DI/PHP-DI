<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Test\UnitTest\Definition;

use DI\Definition\FactoryDefinition;
use DI\Scope;

/**
 * Test class for FactoryDefinition
 *
 * @covers \DI\Definition\FactoryDefinition
 */
class FactoryDefinitionTest extends \PHPUnit_Framework_TestCase
{
    public function testGetters()
    {
        $callable = function () {
        };
        $definition = new FactoryDefinition('foo', $callable);

        $this->assertEquals('foo', $definition->getName());
        $this->assertEquals($callable, $definition->getCallable());
        // Default scope
        $this->assertEquals(Scope::SINGLETON(), $definition->getScope());
    }

    /**
     * Test that the definition accepts callable (not closures)
     */
    public function testAcceptArrayCallable()
    {
        $callable = array($this, 'foo');
        $definition = new FactoryDefinition('foo', $callable);

        $this->assertEquals('foo', $definition->getName());
        $this->assertEquals($callable, $definition->getCallable());
    }

    public function testScope()
    {
        $definition = new FactoryDefinition('foo', function () {
        }, Scope::PROTOTYPE());

        $this->assertEquals(Scope::PROTOTYPE(), $definition->getScope());
    }

    public function testNotCacheable()
    {
        $definition = new FactoryDefinition('foo', function () {
        });

        $this->assertNotInstanceOf('DI\Definition\CacheableDefinition', $definition);
    }
}
