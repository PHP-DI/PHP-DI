<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Test\UnitTest\Definition\Resolver;

use DI\Definition\FactoryDefinition;
use DI\Definition\ClassDefinition;
use DI\Definition\ClassDefinition\MethodInjection;
use DI\Definition\ClassDefinition\PropertyInjection;
use DI\Definition\Resolver\ClassDefinitionResolver;
use DI\Definition\Resolver\ResolverDispatcher;
use DI\Proxy\ProxyFactory;
use EasyMock\EasyMock;
use PHPUnit_Framework_MockObject_MockObject;

/**
 * @covers \DI\Definition\Resolver\ClassDefinitionResolver
 * @covers \DI\Definition\Resolver\ParameterResolver
 */
class ClassDefinitionResolverTest extends \PHPUnit_Framework_TestCase
{
    const FIXTURE_CLASS = 'DI\Test\UnitTest\Definition\Resolver\Fixture\FixtureClass';
    const FIXTURE_CLASS_NO_CONSTRUCTOR = 'DI\Test\UnitTest\Definition\Resolver\Fixture\NoConstructor';
    const FIXTURE_INTERFACE = 'DI\Test\UnitTest\Definition\Resolver\Fixture\FixtureInterface';
    const FIXTURE_ABSTRACT_CLASS = 'DI\Test\UnitTest\Definition\Resolver\Fixture\FixtureAbstractClass';

    /**
     * @var ProxyFactory|PHPUnit_Framework_MockObject_MockObject
     */
    private $proxyFactory;

    /**
     * @var ResolverDispatcher|PHPUnit_Framework_MockObject_MockObject
     */
    private $resolverDispatcher;

    /**
     * @var ClassDefinitionResolver
     */
    private $resolver;

    public function setUp()
    {
        $this->proxyFactory = EasyMock::mock('DI\Proxy\ProxyFactory');
        $this->resolverDispatcher = EasyMock::mock('DI\Definition\Resolver\ResolverDispatcher');

        $this->resolver = new ClassDefinitionResolver($this->resolverDispatcher, $this->proxyFactory);
    }

    public function testResolve()
    {
        $definition = new ClassDefinition(self::FIXTURE_CLASS);
        $definition->addPropertyInjection(new PropertyInjection('prop', 'value1'));
        $definition->setConstructorInjection(MethodInjection::constructor(array('value2')));
        $definition->addMethodInjection(new MethodInjection('method', array('value3')));

        $object = $this->resolver->resolve($definition);

        $this->assertInstanceOf(self::FIXTURE_CLASS, $object);
        $this->assertEquals('value1', $object->prop);
        $this->assertEquals('value2', $object->constructorParam1);
        $this->assertEquals('value3', $object->methodParam1);
    }

    public function testResolveNoConstructorClass()
    {
        $definition = new ClassDefinition(self::FIXTURE_CLASS_NO_CONSTRUCTOR);

        $object = $this->resolver->resolve($definition);
        $this->assertInstanceOf(self::FIXTURE_CLASS_NO_CONSTRUCTOR, $object);
    }

    public function testResolveWithParameters()
    {
        $definition = new ClassDefinition(self::FIXTURE_CLASS);

        $object = $this->resolver->resolve($definition, array('param1' => 'value'));

        $this->assertInstanceOf(self::FIXTURE_CLASS, $object);
        $this->assertEquals('value', $object->constructorParam1);
    }

    /**
     * Check that given parameters override the definition
     */
    public function testResolveWithParametersAndDefinition()
    {
        $definition = new ClassDefinition(self::FIXTURE_CLASS);
        $definition->setConstructorInjection(MethodInjection::constructor(array('foo')));

        $object = $this->resolver->resolve($definition, array('param1' => 'bar'));

        $this->assertInstanceOf(self::FIXTURE_CLASS, $object);
        $this->assertEquals('bar', $object->constructorParam1);
    }

    /**
     * Check that useless parameters are ignored (no error)
     */
    public function testResolveWithUselessParameters()
    {
        $definition = new ClassDefinition(self::FIXTURE_CLASS);

        $object = $this->resolver->resolve($definition, array('param1' => 'value', 'unknown' => 'foo'));

        $this->assertInstanceOf(self::FIXTURE_CLASS, $object);
        $this->assertEquals('value', $object->constructorParam1);
    }

    /**
     * Check that nested definitions are resolved in parameters
     */
    public function testResolveWithNestedDefinitionInParameters()
    {
        $definition = new ClassDefinition(self::FIXTURE_CLASS);
        // The constructor definition uses a nested definition
        $definition->setConstructorInjection(MethodInjection::constructor(array(
            \DI\object(self::FIXTURE_CLASS_NO_CONSTRUCTOR),
        )));

        $this->resolverDispatcher->expects($this->once())
            ->method('resolve')
            ->with($this->isInstanceOf('DI\Definition\ClassDefinition'))
            ->will($this->returnValue('bar'));

        $object = $this->resolver->resolve($definition);

        $this->assertInstanceOf(self::FIXTURE_CLASS, $object);
        $this->assertEquals('bar', $object->constructorParam1);
    }

    /**
     * Check that nested definitions are resolved in properties
     */
    public function testResolveWithNestedDefinitionInProperties()
    {
        $definition = new ClassDefinition(self::FIXTURE_CLASS);
        $definition->addPropertyInjection(new PropertyInjection('prop', \DI\object(self::FIXTURE_CLASS_NO_CONSTRUCTOR)));
        // Unrelated to the test but necessary since it's a mandatory parameter
        $definition->setConstructorInjection(MethodInjection::constructor(array('foo')));

        $this->resolverDispatcher->expects($this->once())
            ->method('resolve')
            ->with($this->isInstanceOf('DI\Definition\ClassDefinition'))
            ->will($this->returnValue('bar'));

        $object = $this->resolver->resolve($definition);

        $this->assertInstanceOf(self::FIXTURE_CLASS, $object);
        $this->assertEquals('bar', $object->prop);
    }

    /**
     * Check that we can inject "null" into parameters and properties
     */
    public function testResolveNullInjections()
    {
        $definition = new ClassDefinition(self::FIXTURE_CLASS);
        $definition->setConstructorInjection(MethodInjection::constructor(array(null)));
        $definition->addPropertyInjection(new PropertyInjection('prop', null));

        $object = $this->resolver->resolve($definition);

        $this->assertInstanceOf(self::FIXTURE_CLASS, $object);
        $this->assertNull($object->constructorParam1);
        $this->assertNull($object->prop);
    }

    public function testDefaultParameterValue()
    {
        $definition = new ClassDefinition(self::FIXTURE_CLASS);
        $definition->setConstructorInjection(MethodInjection::constructor(array('')));
        $definition->addMethodInjection(new MethodInjection('methodDefaultValue'));

        $object = $this->resolver->resolve($definition);

        $this->assertInstanceOf(self::FIXTURE_CLASS, $object);
        $this->assertEquals('defaultValue', $object->methodParam2);
    }

    public function testUnknownClass()
    {
        $message = <<<MESSAGE
Entry foo cannot be resolved: class bar doesn't exist
Full definition:
Object (
    class = #UNKNOWN# bar
    scope = singleton
    lazy = false
)
MESSAGE;
        $this->setExpectedException('DI\Definition\Exception\DefinitionException', $message);

        $definition = new ClassDefinition('foo', 'bar');

        $this->resolver->resolve($definition);
    }

    public function testNotInstantiable()
    {
        $message = <<<MESSAGE
Entry ArrayAccess cannot be resolved: class ArrayAccess is not instantiable
Full definition:
Object (
    class = #NOT INSTANTIABLE# ArrayAccess
    scope = singleton
    lazy = false
)
MESSAGE;
        $this->setExpectedException('DI\Definition\Exception\DefinitionException', $message);

        $definition = new ClassDefinition('ArrayAccess');

        $this->resolver->resolve($definition);
    }

    public function testUndefinedInjection()
    {
        $message = <<<MESSAGE
Entry DI\Test\UnitTest\Definition\Resolver\Fixture\FixtureClass cannot be resolved: The parameter 'param1' of DI\Test\UnitTest\Definition\Resolver\Fixture\FixtureClass::__construct has no value defined or guessable
Full definition:
Object (
    class = DI\Test\UnitTest\Definition\Resolver\Fixture\FixtureClass
    scope = singleton
    lazy = false
)
MESSAGE;
        $this->setExpectedException('DI\Definition\Exception\DefinitionException', $message);

        $definition = new ClassDefinition(self::FIXTURE_CLASS);

        $this->resolver->resolve($definition);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage This definition resolver is only compatible with ClassDefinition objects, DI\Definition\FactoryDefinition given
     */
    public function testInvalidDefinitionType()
    {
        $definition = new FactoryDefinition('foo', function () {
        });

        $this->resolver->resolve($definition);
    }

    public function testIsResolvable()
    {
        $classDefinition = new ClassDefinition(self::FIXTURE_CLASS);
        $this->assertTrue($this->resolver->isResolvable($classDefinition));

        $interfaceDefinition = new ClassDefinition(self::FIXTURE_INTERFACE);
        $this->assertFalse($this->resolver->isResolvable($interfaceDefinition));

        $abstractClassDefinition = new ClassDefinition(self::FIXTURE_ABSTRACT_CLASS);
        $this->assertFalse($this->resolver->isResolvable($abstractClassDefinition));
    }
}
