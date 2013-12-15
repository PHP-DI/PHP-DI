<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.io/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace UnitTests\DI\Definition\Helper;

use DI\Definition\Helper\ClassDefinitionHelper;
use DI\Scope;

/**
 * @covers \DI\Definition\Helper\ClassDefinitionHelper
 */
class ClassDefinitionHelperTest extends \PHPUnit_Framework_TestCase
{
    public function testDefaultValues()
    {
        $helper = new ClassDefinitionHelper();
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
        $helper = new ClassDefinitionHelper('bar');
        $definition = $helper->getDefinition('foo');

        $this->assertEquals('foo', $definition->getName());
        $this->assertEquals('bar', $definition->getClassName());
    }

    public function testScope()
    {
        $helper = new ClassDefinitionHelper();
        $helper->withScope(Scope::PROTOTYPE());
        $definition = $helper->getDefinition('foo');

        $this->assertEquals(Scope::PROTOTYPE(), $definition->getScope());
    }

    public function testConstructor()
    {
        $helper = new ClassDefinitionHelper();
        $helper->withConstructor(1, 2, 3);
        $definition = $helper->getDefinition('foo');

        $this->assertEquals(array(1, 2, 3), $definition->getConstructorInjection()->getParameters());
    }

    public function testPropertyInjections()
    {
        $helper = new ClassDefinitionHelper();
        $helper->withProperty('prop', 1);
        $definition = $helper->getDefinition('foo');

        $this->assertCount(1, $definition->getPropertyInjections());
        $propertyInjection = current($definition->getPropertyInjections());
        $this->assertEquals(1, $propertyInjection->getValue());
    }

    public function testMethodInjections()
    {
        $helper = new ClassDefinitionHelper();
        $helper->withMethod('prop', 1, 2, 3);
        $definition = $helper->getDefinition('foo');

        $this->assertCount(1, $definition->getMethodInjections());
        $methodInjection = current($definition->getMethodInjections());
        $this->assertEquals(array(1, 2, 3), $methodInjection->getParameters());
    }
}
