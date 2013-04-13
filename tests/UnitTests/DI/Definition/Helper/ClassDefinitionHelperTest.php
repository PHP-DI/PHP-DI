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
use DI\Definition\ParameterInjection;
use DI\Scope;

/**
 * Test class for ClassDefinitionHelper
 */
class ClassDefinitionHelperTest extends \PHPUnit_Framework_TestCase
{

    public function testHelper()
    {
        $helper = new ClassDefinitionHelper('foo');
        $helper->bindTo('stdClass')
            ->withScope(Scope::SINGLETON())
            ->withProperty('prop', 'bar')
            ->withConstructor(array('param1', 'param2'))
            ->withMethod('test', array('p1' => 'param1'));

        $definition = $helper->getDefinition();

        $this->assertInstanceOf('DI\Definition\ClassDefinition', $definition);
        $this->assertEquals('foo', $definition->getName());
        $this->assertEquals('stdClass', $definition->getClassName());
        $this->assertEquals(Scope::SINGLETON(), $definition->getScope());

        // Property injection
        $propertyInjections = $definition->getPropertyInjections();
        $this->assertCount(1, $propertyInjections);
        $propertyInjection = array_shift($propertyInjections);
        $this->assertEquals('prop', $propertyInjection->getPropertyName());
        $this->assertEquals('bar', $propertyInjection->getEntryName());

        // Constructor injection
        $constructorInjection = $definition->getConstructorInjection();
        $parameters = $constructorInjection->getParameterInjections();
        $this->assertCount(2, $parameters);
        /** @var $parameter ParameterInjection */
        $parameter = array_shift($parameters);
        $this->assertEquals(0, $parameter->getParameterName());
        $this->assertEquals('param1', $parameter->getEntryName());
        /** @var $parameter ParameterInjection */
        $parameter = array_shift($parameters);
        $this->assertEquals(1, $parameter->getParameterName());
        $this->assertEquals('param2', $parameter->getEntryName());

        // Method injection
        $methodInjections = $definition->getMethodInjections();
        $this->assertCount(1, $methodInjections);
        $methodInjection = array_shift($methodInjections);
        $this->assertEquals('test', $methodInjection->getMethodName());
        $parameters = $methodInjection->getParameterInjections();
        $this->assertCount(1, $parameters);
        /** @var $parameter ParameterInjection */
        $parameter = array_shift($parameters);
        $this->assertEquals('p1', $parameter->getParameterName());
        $this->assertEquals('param1', $parameter->getEntryName());
    }

}
