<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Test\UnitTest\Definition;

use DI\Definition\DecoratorDefinition;
use DI\Definition\ValueDefinition;
use DI\Scope;

/**
 * @covers \DI\Definition\DecoratorDefinition
 */
class DecoratorDefinitionTest extends \PHPUnit_Framework_TestCase
{
    public function test_getters()
    {
        $callable = function () {
        };
        $definition = new DecoratorDefinition('foo', $callable);

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
        $definition = new DecoratorDefinition('foo', $callable);

        $this->assertEquals('foo', $definition->getName());
        $this->assertEquals($callable, $definition->getCallable());
    }

    /**
     * @test
     */
    public function should_not_be_cacheable()
    {
        $definition = new DecoratorDefinition('foo', function () {
        });
        $this->assertNotInstanceOf('DI\Definition\CacheableDefinition', $definition);
    }

    /**
     * @test
     */
    public function should_extend_previous_definition()
    {
        $definition = new DecoratorDefinition('foo', function () {
        });
        $this->assertInstanceOf('DI\Definition\HasSubDefinition', $definition);
        $this->assertEquals($definition->getName(), $definition->getSubDefinitionName());

        $subDefinition = new ValueDefinition('foo', 'bar');
        $definition->setSubDefinition($subDefinition);
        $this->assertSame($subDefinition, $definition->getDecoratedDefinition());
    }
}
