<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Test\UnitTest\Definition\Helper;

use DI\Definition\ClassDefinition\MethodInjection;
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
        $helper->scope(Scope::PROTOTYPE());
        $definition = $helper->getDefinition('foo');

        $this->assertEquals(Scope::PROTOTYPE(), $definition->getScope());
    }

    public function testLazy()
    {
        $helper = new ClassDefinitionHelper();
        $helper->lazy();
        $definition = $helper->getDefinition('foo');

        $this->assertTrue($definition->isLazy());
    }

    public function testConstructor()
    {
        $helper = new ClassDefinitionHelper();
        $helper->constructor(1, 2, 3);
        $definition = $helper->getDefinition('foo');

        $this->assertEquals(array(1, 2, 3), $definition->getConstructorInjection()->getParameters());
    }

    public function testConstructorParameter()
    {
        $helper = new ClassDefinitionHelper();
        $helper->constructorParameter(0, 42);
        $definition = $helper->getDefinition('foo');

        $constructorInjection = $definition->getConstructorInjection();

        $this->assertEquals(42, $constructorInjection->getParameter(0));
    }

    public function testPropertyInjections()
    {
        $helper = new ClassDefinitionHelper();
        $helper->property('prop', 1);
        $definition = $helper->getDefinition('foo');

        $this->assertCount(1, $definition->getPropertyInjections());
        $propertyInjection = current($definition->getPropertyInjections());
        $this->assertEquals(1, $propertyInjection->getValue());
    }

    public function testMethodInjections()
    {
        $helper = new ClassDefinitionHelper();
        $helper->method('method', 1, 2, 3);
        $definition = $helper->getDefinition('foo');

        $this->assertCount(1, $definition->getMethodInjections());
        $methodInjection = current($definition->getMethodInjections());
        $this->assertEquals(array(1, 2, 3), $methodInjection->getParameters());
    }

    public function testMethodParameter()
    {
        $helper = new ClassDefinitionHelper();
        $helper->methodParameter('method', 0, 42);
        $definition = $helper->getDefinition('foo');

        $this->assertCount(1, $definition->getMethodInjections());
        /** @var MethodInjection $methodInjection */
        $methodInjection = current($definition->getMethodInjections());

        $this->assertEquals('method', $methodInjection->getMethodName());
        $this->assertEquals(42, $methodInjection->getParameter(0));
    }

    /**
     * If using methodParameter() for "__construct", then the constructor definition should be updated
     */
    public function testMethodParameterOnConstructor()
    {
        $helper = new ClassDefinitionHelper();
        $helper->methodParameter('__construct', 0, 42);
        $definition = $helper->getDefinition('foo');

        $this->assertCount(0, $definition->getMethodInjections());
        $this->assertNotNull($definition->getConstructorInjection());

        $this->assertEquals(42, $definition->getConstructorInjection()->getParameter(0));
    }

    /**
     * Check using the parameter name, not its index
     */
    public function testMethodParameterByParameterName()
    {
        $helper = new ClassDefinitionHelper();
        $helper->methodParameter('method', 'param2', 'val2');
        $helper->methodParameter('method', 'param1', 'val1');
        $definition = $helper->getDefinition('DI\Test\UnitTest\Definition\Helper\Fixtures\Class1');

        $this->assertCount(1, $definition->getMethodInjections());
        $methodInjection = current($definition->getMethodInjections());

        // Check that injections are in the good order (matching the real parameters order)
        $this->assertEquals(array('val1', 'val2'), $methodInjection->getParameters());
    }
}
