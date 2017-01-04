<?php

namespace DI\Test\UnitTest\Definition\Resolver;

use DI\Definition\Exception\DefinitionException;
use DI\Definition\ObjectDefinition;
use DI\Definition\ObjectDefinition\MethodInjection;
use DI\Definition\ObjectDefinition\PropertyInjection;
use DI\Definition\Resolver\DefinitionResolver;
use DI\Definition\Resolver\ObjectCreator;
use DI\Proxy\ProxyFactory;
use DI\Test\UnitTest\Definition\Resolver\Fixture\FixtureAbstractClass;
use DI\Test\UnitTest\Definition\Resolver\Fixture\FixtureClass;
use DI\Test\UnitTest\Definition\Resolver\Fixture\FixtureInterface;
use DI\Test\UnitTest\Definition\Resolver\Fixture\NoConstructor;
use EasyMock\EasyMock;
use PHPUnit_Framework_MockObject_MockObject;

/**
 * @covers \DI\Definition\Resolver\ObjectCreator
 * @covers \DI\Definition\Resolver\ParameterResolver
 */
class ObjectCreatorTest extends \PHPUnit_Framework_TestCase
{
    use EasyMock;

    /**
     * @var ProxyFactory|PHPUnit_Framework_MockObject_MockObject
     */
    private $proxyFactory;

    /**
     * @var DefinitionResolver|PHPUnit_Framework_MockObject_MockObject
     */
    private $parentResolver;

    /**
     * @var ObjectCreator
     */
    private $resolver;

    public function setUp()
    {
        $this->proxyFactory = $this->easyMock(ProxyFactory::class);
        $this->parentResolver = $this->easyMock(DefinitionResolver::class);

        $this->resolver = new ObjectCreator($this->parentResolver, $this->proxyFactory);
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
            \DI\create(NoConstructor::class),
        ]));

        $this->parentResolver->expects($this->once())
            ->method('resolve')
            ->with($this->isInstanceOf(ObjectDefinition::class))
            ->will($this->returnValue('bar'));

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
        $definition->addPropertyInjection(new PropertyInjection('prop', \DI\create(NoConstructor::class)));
        // Unrelated to the test but necessary since it's a mandatory parameter
        $definition->setConstructorInjection(MethodInjection::constructor(['foo']));

        $this->parentResolver->expects($this->once())
            ->method('resolve')
            ->with($this->isInstanceOf(ObjectDefinition::class))
            ->will($this->returnValue('bar'));

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
        $message = <<<'MESSAGE'
Entry "foo" cannot be resolved: the class doesn't exist
Full definition:
Object (
    class = #UNKNOWN# bar
    scope = singleton
    lazy = false
)
MESSAGE;
        $this->setExpectedException(DefinitionException::class, $message);

        $definition = new ObjectDefinition('foo', 'bar');

        $this->resolver->resolve($definition);
    }

    public function testNotInstantiable()
    {
        $message = <<<'MESSAGE'
Entry "ArrayAccess" cannot be resolved: the class is not instantiable
Full definition:
Object (
    class = #NOT INSTANTIABLE# ArrayAccess
    scope = singleton
    lazy = false
)
MESSAGE;
        $this->setExpectedException(DefinitionException::class, $message);

        $definition = new ObjectDefinition('ArrayAccess');

        $this->resolver->resolve($definition);
    }

    public function testUndefinedInjection()
    {
        $message = <<<'MESSAGE'
Entry "DI\Test\UnitTest\Definition\Resolver\Fixture\FixtureClass" cannot be resolved: Parameter $param1 of __construct() has no value defined or guessable
Full definition:
Object (
    class = DI\Test\UnitTest\Definition\Resolver\Fixture\FixtureClass
    scope = singleton
    lazy = false
)
MESSAGE;
        $this->setExpectedException(DefinitionException::class, $message);

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
