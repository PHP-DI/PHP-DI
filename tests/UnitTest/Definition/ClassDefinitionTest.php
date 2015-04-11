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
use DI\Definition\ClassDefinition\MethodInjection;
use DI\Definition\ClassDefinition\PropertyInjection;
use DI\Definition\ValueDefinition;
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

        $definition->setClassName('classname');
        $this->assertEquals('classname', $definition->getClassName());
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

    /**
     * @test
     * @expectedException \DI\Definition\Exception\DefinitionException
     * @expectedExceptionMessage Container entry 'foo' extends entry 'bar' which is not an object
     */
    public function should_only_accept_compatible_subdefinitions()
    {
        $definition = new ClassDefinition('foo', 'bar');
        $definition->setSubDefinition(new ValueDefinition('bar', 'Hello'));
    }

    /**
     * @test
     */
    public function should_merge_with_its_subdefinition()
    {
        $definition = new ClassDefinition('foo', 'bar');
        $definition->addPropertyInjection(new PropertyInjection('property1', 'Property1'));
        $definition->addPropertyInjection(new PropertyInjection('property2', 'Property2'));
        $definition->addMethodInjection(new MethodInjection('method1'));
        $definition->addMethodInjection(new MethodInjection('method2'));

        $subDefinition = new ClassDefinition('bar');
        $subDefinition->setLazy(true);
        $subDefinition->setScope(Scope::PROTOTYPE());
        $subDefinition->setConstructorInjection(MethodInjection::constructor());
        $subDefinition->addPropertyInjection(new PropertyInjection('property1', 'Property1'));
        $subDefinition->addPropertyInjection(new PropertyInjection('property3', 'Property3'));
        $subDefinition->addMethodInjection(new MethodInjection('method1'));
        $subDefinition->addMethodInjection(new MethodInjection('method3'));

        $definition->setSubDefinition($subDefinition);

        $this->assertEquals('foo', $definition->getName());
        $this->assertEquals('bar', $definition->getClassName());
        $this->assertTrue($definition->isLazy());
        $this->assertEquals(Scope::PROTOTYPE(), $definition->getScope());
        $this->assertNotNull($definition->getConstructorInjection());
        $this->assertCount(3, $definition->getPropertyInjections());
        $this->assertCount(3, $definition->getMethodInjections());
    }
}
