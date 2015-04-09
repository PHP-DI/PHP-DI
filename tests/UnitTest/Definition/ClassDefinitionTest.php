<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Test\UnitTest\Definition;

use DI\Definition\ClassDefinition;
use DI\Scope;

/**
 * @covers \DI\Definition\ClassDefinition
 */
class ClassDefinitionTest extends \PHPUnit_Framework_TestCase
{
    public function test_getters_setters()
    {
        $definition = new ClassDefinition('foo', 'bar');
        $definition->setLazy(true);
        $definition->setScope(Scope::PROTOTYPE());

        $this->assertEquals('foo', $definition->getName());
        $this->assertEquals('bar', $definition->getClassName());
        $this->assertTrue($definition->isLazy());
        $this->assertEquals(Scope::PROTOTYPE(), $definition->getScope());
    }

    public function test_defaults()
    {
        $definition = new ClassDefinition('foo');

        $this->assertEquals('foo', $definition->getName());
        $this->assertEquals('foo', $definition->getClassName());
        $this->assertFalse($definition->isLazy());
        $this->assertEquals(Scope::SINGLETON(), $definition->getScope());
        $this->assertNull($definition->getConstructorInjection());
        $this->assertEmpty($definition->getPropertyInjections());
        $this->assertEmpty($definition->getMethodInjections());
    }

    public function should_be_cacheable()
    {
        $this->assertInstanceOf('DI\Definition\CacheableDefinition', new ClassDefinition('foo'));
    }
}
