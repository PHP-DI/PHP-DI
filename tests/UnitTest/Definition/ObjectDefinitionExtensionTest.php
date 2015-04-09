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
use DI\Definition\ObjectDefinitionExtension;
use DI\Definition\ClassDefinition\MethodInjection;
use DI\Definition\ClassDefinition\PropertyInjection;
use DI\Definition\ValueDefinition;
use DI\Scope;

/**
 * @covers \DI\Definition\ObjectDefinitionExtension
 */
class ObjectDefinitionExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @expectedException \DI\Definition\Exception\DefinitionException
     * @expectedExceptionMessage Container entry 'foo' extends entry 'bar' which is not an object
     */
    public function should_only_accept_compatible_subdefinitions()
    {
        $definition = new ObjectDefinitionExtension('foo');
        $definition->setSubDefinition(new ValueDefinition('bar', 'Hello'));
    }

    /**
     * @test
     */
    public function should_merge_with_its_subdefinition()
    {
        $extension = new ObjectDefinitionExtension('foo', 'bar');
        $extension->addPropertyInjection(new PropertyInjection('property1', 'Property1'));
        $extension->addPropertyInjection(new PropertyInjection('property2', 'Property2'));
        $extension->addMethodInjection(new MethodInjection('method1'));
        $extension->addMethodInjection(new MethodInjection('method2'));

        $subDefinition = new ClassDefinition('foo');
        $subDefinition->setLazy(true);
        $subDefinition->setScope(Scope::PROTOTYPE());
        $subDefinition->setConstructorInjection(MethodInjection::constructor());
        $subDefinition->addPropertyInjection(new PropertyInjection('property1', 'Property1'));
        $subDefinition->addPropertyInjection(new PropertyInjection('property3', 'Property3'));
        $subDefinition->addMethodInjection(new MethodInjection('method1'));
        $subDefinition->addMethodInjection(new MethodInjection('method3'));

        $extension->setSubDefinition($subDefinition);

        $this->assertEquals('foo', $extension->getName());
        $this->assertEquals('bar', $extension->getClassName());
        $this->assertTrue($extension->isLazy());
        $this->assertEquals(Scope::PROTOTYPE(), $extension->getScope());
        $this->assertNotNull($extension->getConstructorInjection());
        $this->assertCount(3, $extension->getPropertyInjections());
        $this->assertCount(3, $extension->getMethodInjections());
    }
}
