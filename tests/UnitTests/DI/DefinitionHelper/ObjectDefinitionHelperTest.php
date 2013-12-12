<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.io/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace UnitTests\DI\DefinitionHelper;

use DI\DefinitionHelper\ObjectDefinitionHelper;
use DI\Scope;

/**
 * @covers \DI\DefinitionHelper\ObjectDefinitionHelper
 */
class ObjectDefinitionHelperTest extends \PHPUnit_Framework_TestCase
{
    public function testDefaultValues()
    {
        $helper = new ObjectDefinitionHelper();
        $definition = $helper->getDefinition('foo');

        $this->assertEquals('foo', $definition->getName());
        $this->assertEquals('foo', $definition->getClassName());
        $this->assertEquals(Scope::SINGLETON(), $definition->getScope());
        $this->assertNull($definition->getConstructorInjection());
        $this->assertEmpty($definition->getPropertyInjections());
        $this->assertEmpty($definition->getMethodInjections());
    }

    public function testClassName()
    {
        $helper = new ObjectDefinitionHelper('bar');
        $definition = $helper->getDefinition('foo');

        $this->assertEquals('foo', $definition->getName());
        $this->assertEquals('bar', $definition->getClassName());
    }

    public function testScope()
    {
        $helper = new ObjectDefinitionHelper();
        $helper->withScope(Scope::PROTOTYPE());
        $definition = $helper->getDefinition('foo');

        $this->assertEquals(Scope::PROTOTYPE(), $definition->getScope());
    }

    public function testConstructor()
    {
        $helper = new ObjectDefinitionHelper();
        $helper->withConstructor(1, 2, 3);
        $definition = $helper->getDefinition('foo');

        $this->assertEquals(array(1, 2, 3), $definition->getConstructorInjection()->getParameters());
    }

    public function testPropertyInjections()
    {
        $helper = new ObjectDefinitionHelper();
        $helper->withProperty('prop', 1);
        $definition = $helper->getDefinition('foo');

        $this->assertCount(1, $definition->getPropertyInjections());
        $propertyInjection = current($definition->getPropertyInjections());
        $this->assertEquals(1, $propertyInjection->getValue());
    }

    public function testMethodInjections()
    {
        $helper = new ObjectDefinitionHelper();
        $helper->withMethod('prop', 1, 2, 3);
        $definition = $helper->getDefinition('foo');

        $this->assertCount(1, $definition->getMethodInjections());
        $methodInjection = current($definition->getMethodInjections());
        $this->assertEquals(array(1, 2, 3), $methodInjection->getParameters());
    }
}
