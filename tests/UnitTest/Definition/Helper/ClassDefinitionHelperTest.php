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
    /**
     * @test
     */
    public function test_default_config()
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

    /**
     * @test
     */
    public function allows_to_define_the_class_name()
    {
        $helper = new ClassDefinitionHelper('bar');
        $definition = $helper->getDefinition('foo');

        $this->assertEquals('foo', $definition->getName());
        $this->assertEquals('bar', $definition->getClassName());
    }

    /**
     * @test
     */
    public function allows_to_define_the_scope()
    {
        $helper = new ClassDefinitionHelper();
        $helper->scope(Scope::PROTOTYPE());
        $definition = $helper->getDefinition('foo');

        $this->assertEquals(Scope::PROTOTYPE(), $definition->getScope());
    }

    /**
     * @test
     */
    public function allows_to_declare_the_service_as_lazy()
    {
        $helper = new ClassDefinitionHelper();
        $helper->lazy();
        $definition = $helper->getDefinition('foo');

        $this->assertTrue($definition->isLazy());
    }

    /**
     * @test
     */
    public function allows_to_define_constructor_parameters()
    {
        $helper = new ClassDefinitionHelper();
        $helper->constructor(1, 2, 3);
        $definition = $helper->getDefinition('foo');

        $this->assertEquals(array(1, 2, 3), $definition->getConstructorInjection()->getParameters());
    }

    /**
     * @test
     */
    public function allows_to_override_a_parameter_injection()
    {
        $helper = new ClassDefinitionHelper();
        $helper->constructorParameter(0, 42);
        $definition = $helper->getDefinition('foo');

        $constructorInjection = $definition->getConstructorInjection();

        $this->assertEquals(42, $constructorInjection->getParameter(0));
    }

    /**
     * @test
     */
    public function allows_to_define_a_property_injection()
    {
        $helper = new ClassDefinitionHelper();
        $helper->property('prop', 1);
        $definition = $helper->getDefinition('foo');

        $this->assertCount(1, $definition->getPropertyInjections());
        $propertyInjection = current($definition->getPropertyInjections());
        $this->assertEquals(1, $propertyInjection->getValue());
    }

    /**
     * @test
     */
    public function allows_to_define_a_method_call()
    {
        $helper = new ClassDefinitionHelper();
        $helper->method('method', 1, 2, 3);
        $definition = $helper->getDefinition('foo');

        $this->assertCount(1, $definition->getMethodInjections());
        $methodInjection = current($definition->getMethodInjections());
        $this->assertEquals(array(1, 2, 3), $methodInjection->getParameters());
    }

    /**
     * @test
     */
    public function allows_to_define_multiple_method_calls()
    {
        $helper = new ClassDefinitionHelper();
        $helper->method('method', 1, 2);
        $helper->method('method', 3, 4);
        $definition = $helper->getDefinition('foo');

        $methodCalls = $definition->getMethodInjections();
        $this->assertCount(2, $methodCalls);
        $methodInjection = array_shift($methodCalls);
        $this->assertEquals(array(1, 2), $methodInjection->getParameters());
        $methodInjection = array_shift($methodCalls);
        $this->assertEquals(array(3, 4), $methodInjection->getParameters());
    }

    /**
     * @test
     */
    public function allows_to_override_a_parameter_injection_by_index()
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
     * Check using the parameter name, not its index
     */
    public function allows_to_override_a_parameter_injection_by_name()
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

    /**
     * If using methodParameter() for "__construct", then the constructor definition should be updated
     */
    public function should_update_constructor_definition_if_overriding_parameter_for_constructor()
    {
        $helper = new ClassDefinitionHelper();
        $helper->methodParameter('__construct', 0, 42);
        $definition = $helper->getDefinition('foo');

        $this->assertCount(0, $definition->getMethodInjections());
        $this->assertNotNull($definition->getConstructorInjection());

        $this->assertEquals(42, $definition->getConstructorInjection()->getParameter(0));
    }
}
