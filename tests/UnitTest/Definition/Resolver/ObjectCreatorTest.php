<?php

declare(strict_types=1);

namespace DI\Test\UnitTest\Definition\Resolver;

use DI\Definition\ObjectDefinition;
use DI\Definition\ObjectDefinition\MethodInjection;
use DI\Definition\ObjectDefinition\PropertyInjection;
use DI\Definition\Resolver\DefinitionResolverInterface;
use DI\Definition\Resolver\ObjectCreator;
use DI\Proxy\ProxyFactory;
use DI\Test\UnitTest\Definition\Resolver\Fixture\FixtureAbstractClass;
use DI\Test\UnitTest\Definition\Resolver\Fixture\FixtureClass;
use DI\Test\UnitTest\Definition\Resolver\Fixture\FixtureInterface;
use DI\Test\UnitTest\Definition\Resolver\Fixture\NoConstructor;
use EasyMock\EasyMock;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use DI\Definition\Exception\InvalidDefinition;

/**
 * @covers \DI\Definition\Resolver\ObjectCreator
 * @covers \DI\Definition\Resolver\ParameterResolver
 */
class ObjectCreatorTest extends TestCase
{
    use EasyMock;

    private MockObject|DefinitionResolverInterface $parentResolver;

    private ObjectCreator $resolver;

    public function setUp(): void
    {
        $proxyFactory = $this->easyMock(ProxyFactory::class);
        $this->parentResolver = $this->easyMock(DefinitionResolverInterface::class);

        $this->resolver = new ObjectCreator($this->parentResolver, $proxyFactory);
    }

    public function testResolve()
    {
        $definition = new ObjectDefinition(FixtureClass::class);
        $definition->addPropertyInjection(new PropertyInjection('prop', 'value1'));
        $definition->setConstructorInjection(MethodInjection::constructor(['value2']));
        $definition->addMethodInjection(new MethodInjection('method', ['value3']));

        $object = $this->resolver->resolve($definition);

        $this->assertInstanceOf(FixtureClass::class, $object);
        $this->assertEquals('value1', $object->prop);
        $this->assertEquals('value2', $object->constructorParam1);
        $this->assertEquals('value3', $object->methodParam1);
    }

    public function testResolveNoConstructorClass()
    {
        $definition = new ObjectDefinition(NoConstructor::class);

        $object = $this->resolver->resolve($definition);
        $this->assertInstanceOf(NoConstructor::class, $object);
    }

    public function testResolveWithParameters()
    {
        $definition = new ObjectDefinition(FixtureClass::class);

        $object = $this->resolver->resolve($definition, ['param1' => 'value']);

        $this->assertInstanceOf(FixtureClass::class, $object);
        $this->assertEquals('value', $object->constructorParam1);
    }

    /**
     * Check that given parameters override the definition.
     */
    public function testResolveWithParametersAndDefinition()
    {
        $definition = new ObjectDefinition(FixtureClass::class);
        $definition->setConstructorInjection(MethodInjection::constructor(['foo']));

        $object = $this->resolver->resolve($definition, ['param1' => 'bar']);

        $this->assertInstanceOf(FixtureClass::class, $object);
        $this->assertEquals('bar', $object->constructorParam1);
    }

    /**
     * Check that useless parameters are ignored (no error).
     */
    public function testResolveWithUselessParameters()
    {
        $definition = new ObjectDefinition(FixtureClass::class);

        $object = $this->resolver->resolve($definition, ['param1' => 'value', 'unknown' => 'foo']);

        $this->assertInstanceOf(FixtureClass::class, $object);
        $this->assertEquals('value', $object->constructorParam1);
    }

    /**
     * Check that nested definitions are resolved in parameters.
     */
    public function testResolveWithNestedDefinitionInParameters()
    {
        $definition = new ObjectDefinition(FixtureClass::class);
        // The constructor definition uses a nested definition
        $definition->setConstructorInjection(MethodInjection::constructor([
            new ObjectDefinition('', NoConstructor::class),
        ]));

        $this->parentResolver->expects($this->once())
            ->method('resolve')
            ->with($this->isInstanceOf(ObjectDefinition::class))
            ->willReturn('bar');

        $object = $this->resolver->resolve($definition);

        $this->assertInstanceOf(FixtureClass::class, $object);
        $this->assertEquals('bar', $object->constructorParam1);
    }

    /**
     * Check that nested definitions are resolved in properties.
     */
    public function testResolveWithNestedDefinitionInProperties()
    {
        $definition = new ObjectDefinition(FixtureClass::class);
        $definition->addPropertyInjection(new PropertyInjection('prop', new ObjectDefinition('', NoConstructor::class)));
        // Unrelated to the test but necessary since it's a mandatory parameter
        $definition->setConstructorInjection(MethodInjection::constructor(['foo']));

        $this->parentResolver->expects($this->once())
            ->method('resolve')
            ->with($this->isInstanceOf(ObjectDefinition::class))
            ->willReturn('bar');

        $object = $this->resolver->resolve($definition);

        $this->assertInstanceOf(FixtureClass::class, $object);
        $this->assertEquals('bar', $object->prop);
    }

    /**
     * Check that we can inject "null" into parameters and properties.
     */
    public function testResolveNullInjections()
    {
        $definition = new ObjectDefinition(FixtureClass::class);
        $definition->setConstructorInjection(MethodInjection::constructor([null]));
        $definition->addPropertyInjection(new PropertyInjection('prop', null));

        $object = $this->resolver->resolve($definition);

        $this->assertInstanceOf(FixtureClass::class, $object);
        $this->assertNull($object->constructorParam1);
        $this->assertNull($object->prop);
    }

    public function testDefaultParameterValue()
    {
        $definition = new ObjectDefinition(FixtureClass::class);
        $definition->setConstructorInjection(MethodInjection::constructor(['']));
        $definition->addMethodInjection(new MethodInjection('methodDefaultValue'));

        $object = $this->resolver->resolve($definition);

        $this->assertInstanceOf(FixtureClass::class, $object);
        $this->assertEquals('defaultValue', $object->methodParam2);
    }

    public function testUnknownClass()
    {
        $this->expectException(InvalidDefinition::class);
        $message = <<<'MESSAGE'
Entry "foo" cannot be resolved: the class doesn't exist
Full definition:
Object (
    class = #UNKNOWN# bar
    lazy = false
)
MESSAGE;
        $this->expectExceptionMessage($message);

        $definition = new ObjectDefinition('foo', 'bar');

        $this->resolver->resolve($definition);
    }

    public function testNotInstantiable()
    {
        $this->expectException(InvalidDefinition::class);
        $message = <<<'MESSAGE'
Entry "ArrayAccess" cannot be resolved: the class is not instantiable
Full definition:
Object (
    class = #NOT INSTANTIABLE# ArrayAccess
    lazy = false
)
MESSAGE;
        $this->expectExceptionMessage($message);

        $definition = new ObjectDefinition('ArrayAccess');

        $this->resolver->resolve($definition);
    }

    public function testUndefinedInjection()
    {
        $this->expectException(InvalidDefinition::class);
        $message = <<<'MESSAGE'
Entry "DI\Test\UnitTest\Definition\Resolver\Fixture\FixtureClass" cannot be resolved: Parameter $param1 of __construct() has no value defined or guessable
Full definition:
Object (
    class = DI\Test\UnitTest\Definition\Resolver\Fixture\FixtureClass
    lazy = false
)
MESSAGE;
        $this->expectExceptionMessage($message);

        $definition = new ObjectDefinition(FixtureClass::class);

        $this->resolver->resolve($definition);
    }

    public function testIsResolvable()
    {
        $classDefinition = new ObjectDefinition(FixtureClass::class);
        $this->assertTrue($this->resolver->isResolvable($classDefinition));

        $interfaceDefinition = new ObjectDefinition(FixtureInterface::class);
        $this->assertFalse($this->resolver->isResolvable($interfaceDefinition));

        $abstractClassDefinition = new ObjectDefinition(FixtureAbstractClass::class);
        $this->assertFalse($this->resolver->isResolvable($abstractClassDefinition));
    }
}
