<?php

declare(strict_types=1);

namespace DI\Test\UnitTest\Definition;

use DI\Definition\ObjectDefinition;
use DI\Definition\ObjectDefinition\MethodInjection;
use DI\Definition\ObjectDefinition\PropertyInjection;
use DI\Test\UnitTest\Definition\Fixture\NonInstantiableClass;
use PHPUnit\Framework\TestCase;

/**
 * @covers \DI\Definition\ObjectDefinition
 */
class ObjectDefinitionTest extends TestCase
{
    public function test_getters_setters()
    {
        $definition = new ObjectDefinition('foo', 'bar');
        $definition->setLazy(true);

        $this->assertEquals('foo', $definition->getName());
        $this->assertEquals('bar', $definition->getClassName());
        $this->assertTrue($definition->isLazy());

        $definition->setClassName('classname');
        $this->assertEquals('classname', $definition->getClassName());
    }

    public function test_defaults()
    {
        $definition = new ObjectDefinition('foo');

        $this->assertEquals('foo', $definition->getName());
        $this->assertEquals('foo', $definition->getClassName());
        $this->assertFalse($definition->isLazy());
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

    public function test_replace_wildcards()
    {
        $definition = new ObjectDefinition('class', 'Foo*\Bar*\Baz*');
        $definition->replaceWildcards(['1', '2', '3']);
        $this->assertEquals('Foo1\Bar2\Baz3', $definition->getClassName());
    }

    public function test_replace_wildcards_with_no_classname_defined()
    {
        $definition = new ObjectDefinition('Foo*\Bar*\Baz*');
        $definition->replaceWildcards(['1', '2', '3']);
        $this->assertEquals('Foo1\Bar2\Baz3', $definition->getClassName());
    }

    public function test_replace_wildcards_with_extra_replacements()
    {
        $definition = new ObjectDefinition('Foo*\Bar\Baz');
        $definition->replaceWildcards(['1', '2', '3']);
        $this->assertEquals('Foo1\Bar\Baz', $definition->getClassName());
    }

    public function test_replace_wildcards_with_missing_replacements()
    {
        $definition = new ObjectDefinition('Foo*\Bar*\Baz*');
        $definition->replaceWildcards(['1']);
        $this->assertEquals('Foo1\Bar*\Baz*', $definition->getClassName());
    }

    public function test_replace_wildcards_with_no_wildcards()
    {
        $definition = new ObjectDefinition('Foo\Bar\Baz');
        $definition->replaceWildcards(['1', '2', '3']);
        $this->assertEquals('Foo\Bar\Baz', $definition->getClassName());
    }
}
