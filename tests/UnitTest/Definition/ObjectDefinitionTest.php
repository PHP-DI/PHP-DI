<?php

namespace DI\Test\UnitTest\Definition;

use DI\Definition\CacheableDefinition;
use DI\Definition\ObjectDefinition;
use DI\Definition\ObjectDefinition\MethodInjection;
use DI\Definition\ObjectDefinition\PropertyInjection;
use DI\Scope;
use DI\Test\UnitTest\Definition\Fixture\NonInstantiableClass;

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

    public function test_class_exists()
    {
        $definition = new ObjectDefinition('foo');
        self::assertFalse($definition->classExists());
        $definition = new ObjectDefinition(self::class);
        self::assertTrue($definition->classExists());
    }

    public function test_is_instantiable()
    {
        $definition = new ObjectDefinition('foo');
        self::assertFalse($definition->isInstantiable());
        $definition = new ObjectDefinition(NonInstantiableClass::class);
        self::assertFalse($definition->isInstantiable());
        $definition = new ObjectDefinition(\stdClass::class);
        self::assertTrue($definition->classExists());
    }

    public function test_property_injections()
    {
        $definition = new ObjectDefinition('foo');
        $definition->addPropertyInjection(new PropertyInjection('property1', 'Property1'));
        $definition->addPropertyInjection(new PropertyInjection('property2', 'Property2'));
        $this->assertEquals([
            'property1' => new PropertyInjection('property1', 'Property1'),
            'property2' => new PropertyInjection('property2', 'Property2'),
        ], $definition->getPropertyInjections());
    }

    public function test_method_injections()
    {
        $definition = new ObjectDefinition('foo');
        $definition->setConstructorInjection(MethodInjection::constructor());
        $definition->addMethodInjection(new MethodInjection('method1', ['foo']));
        $definition->addMethodInjection(new MethodInjection('method2'));

        $this->assertNotNull($definition->getConstructorInjection());
        $this->assertEquals([
            new MethodInjection('method1', ['foo']),
            new MethodInjection('method2'),
        ], $definition->getMethodInjections());
    }

    /**
     * @test
     */
    public function should_be_cacheable()
    {
        $this->assertInstanceOf(CacheableDefinition::class, new ObjectDefinition('foo'));
    }
}
