<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Test\UnitTest\Definition;

use DI\Definition\ObjectDefinition;
use DI\Definition\ObjectDefinition\MethodInjection;
use DI\Definition\ObjectDefinition\PropertyInjection;
use DI\Definition\ValueDefinition;
use DI\Scope;

/**
 * @covers \DI\Definition\ObjectDefinition
 */
class ObjectDefinitionTest extends \PHPUnit_Framework_TestCase
{
    public function test_getters_setters()
    {
        $definition = new ObjectDefinition('foo', 'bar');
        $definition->setLazy(true);
        $definition->setScope(Scope::PROTOTYPE);

        $this->assertEquals('foo', $definition->getName());
        $this->assertEquals('bar', $definition->getClassName());
        $this->assertTrue($definition->isLazy());
        $this->assertEquals(Scope::PROTOTYPE, $definition->getScope());

        $definition->setClassName('classname');
        $this->assertEquals('classname', $definition->getClassName());
    }

    public function test_defaults()
    {
        $definition = new ObjectDefinition('foo');

        $this->assertEquals('foo', $definition->getName());
        $this->assertEquals('foo', $definition->getClassName());
        $this->assertFalse($definition->isLazy());
        $this->assertEquals(Scope::SINGLETON, $definition->getScope());
        $this->assertNull($definition->getConstructorInjection());
        $this->assertEmpty($definition->getPropertyInjections());
        $this->assertEmpty($definition->getMethodInjections());
    }

    public function should_be_cacheable()
    {
        $this->assertInstanceOf('DI\Definition\CacheableDefinition', new ObjectDefinition('foo'));
    }

    /**
     * @test
     * @expectedException \DI\Definition\Exception\DefinitionException
     * @expectedExceptionMessage Container entry 'foo' extends entry 'bar' which is not an object
     */
    public function should_only_accept_compatible_subdefinitions()
    {
        $definition = new ObjectDefinition('foo', 'bar');
        $definition->setSubDefinition(new ValueDefinition('bar', 'Hello'));
    }

    /**
     * @test
     */
    public function should_merge_with_its_subdefinition()
    {
        $definition = new ObjectDefinition('foo', 'bar');
        $definition->addPropertyInjection(new PropertyInjection('property1', 'Property1'));
        $definition->addPropertyInjection(new PropertyInjection('property2', 'Property2'));
        $definition->addMethodInjection(new MethodInjection('method1', ['foo']));
        $definition->addMethodInjection(new MethodInjection('method2'));

        $subDefinition = new ObjectDefinition('bar');
        $subDefinition->setLazy(true);
        $subDefinition->setScope(Scope::PROTOTYPE);
        $subDefinition->setConstructorInjection(MethodInjection::constructor());
        $subDefinition->addPropertyInjection(new PropertyInjection('property1', 'Property1'));
        $subDefinition->addPropertyInjection(new PropertyInjection('property3', 'Property3'));
        $subDefinition->addMethodInjection(new MethodInjection('method1', ['bar']));
        $subDefinition->addMethodInjection(new MethodInjection('method3'));

        $definition->setSubDefinition($subDefinition);

        $this->assertEquals('foo', $definition->getName());
        $this->assertEquals('bar', $definition->getClassName());
        $this->assertTrue($definition->isLazy());
        $this->assertEquals(Scope::PROTOTYPE, $definition->getScope());
        $this->assertNotNull($definition->getConstructorInjection());
        $this->assertCount(3, $definition->getPropertyInjections());
        $this->assertCount(3, $definition->getMethodInjections());
        $this->assertEquals([
            new MethodInjection('method1', ['foo']),
            new MethodInjection('method2'),
            new MethodInjection('method3'),
        ], $definition->getMethodInjections());
    }

    /**
     * @test
     */
    public function should_merge_multiple_method_calls()
    {
        $definition = new ObjectDefinition('foo');
        $definition->addMethodInjection(new MethodInjection('method1'));
        $definition->addMethodInjection(new MethodInjection('method2', ['bam']));
        $definition->addMethodInjection(new MethodInjection('method2', ['baz']));

        $subDefinition = new ObjectDefinition('bar');
        $subDefinition->addMethodInjection(new MethodInjection('method1', ['bar']));
        $subDefinition->addMethodInjection(new MethodInjection('method2', ['foo', 'bar']));
        $subDefinition->addMethodInjection(new MethodInjection('method3'));
        $subDefinition->addMethodInjection(new MethodInjection('method3'));

        $definition->setSubDefinition($subDefinition);

        $this->assertEquals([
            new MethodInjection('method1', ['bar']),
            new MethodInjection('method2', ['bam', 'bar']),
            new MethodInjection('method2', ['baz']),
            new MethodInjection('method3'),
            new MethodInjection('method3'),
        ], $definition->getMethodInjections());
    }
}
