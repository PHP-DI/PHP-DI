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
 * @covers \DI\Definition\FactoryDefinition
 */
class FactoryDefinitionTest extends \PHPUnit_Framework_TestCase
{
    public function test_getters()
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
     * @test
     */
    public function should_accept_callables_other_than_closures()
    {
        $callable = array($this, 'foo');
        $definition = new FactoryDefinition('foo', $callable);

        $this->assertEquals('foo', $definition->getName());
        $this->assertEquals($callable, $definition->getCallable());
    }

    /**
     * @test
     */
    public function can_have_a_custom_scope()
    {
        $definition = new FactoryDefinition('foo', function () {
        }, Scope::PROTOTYPE());

        $this->assertEquals(Scope::PROTOTYPE(), $definition->getScope());
    }

    /**
     * @test
     */
    public function should_not_be_cacheable()
    {
        $definition = new FactoryDefinition('foo', function () {
        });

        $this->assertNotInstanceOf('DI\Definition\CacheableDefinition', $definition);
    }
}
