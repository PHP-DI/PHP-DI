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
use DI\Definition\EntryReference;
use DI\Definition\Resolver\ClassDefinitionResolver;
use DI\Proxy\ProxyFactory;

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

    public function testResolve()
    {
        $definition = new ClassDefinition(self::FIXTURE_CLASS);
        $definition->addPropertyInjection(new PropertyInjection('prop', 'value1'));
        $definition->setConstructorInjection(MethodInjection::constructor(array('value2')));
        $definition->addMethodInjection(new MethodInjection('method', array('value3')));
        $resolver = $this->buildResolver();

        $object = $resolver->resolve($definition);

        $this->assertInstanceOf(self::FIXTURE_CLASS, $object);
        $this->assertEquals('value1', $object->prop);
        $this->assertEquals('value2', $object->constructorParam1);
        $this->assertEquals('value3', $object->methodParam1);
    }

    public function testResolveNoConstructorClass()
    {
        $definition = new ClassDefinition(self::FIXTURE_CLASS_NO_CONSTRUCTOR);
        $resolver = $this->buildResolver();

        $object = $resolver->resolve($definition);
        $this->assertInstanceOf(self::FIXTURE_CLASS_NO_CONSTRUCTOR, $object);
    }

    public function testResolveWithParameters()
    {
        $definition = new ClassDefinition(self::FIXTURE_CLASS);
        $resolver = $this->buildResolver();

        $object = $resolver->resolve($definition, array('param1' => 'value'));

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
        $resolver = $this->buildResolver();

        $object = $resolver->resolve($definition, array('param1' => 'bar'));

        $this->assertInstanceOf(self::FIXTURE_CLASS, $object);
        $this->assertEquals('bar', $object->constructorParam1);
    }

    /**
     * Check that useless parameters are ignored (no error)
     */
    public function testResolveWithUselessParameters()
    {
        $definition = new ClassDefinition(self::FIXTURE_CLASS);
        $resolver = $this->buildResolver();

        $object = $resolver->resolve($definition, array('param1' => 'value', 'unknown' => 'foo'));

        $this->assertInstanceOf(self::FIXTURE_CLASS, $object);
        $this->assertEquals('value', $object->constructorParam1);
    }

    /**
     * Check that entry references (in the definition) are resolved using the container
     */
    public function testResolveWithEntryReference()
    {
        $definition = new ClassDefinition(self::FIXTURE_CLASS);
        // The constructor definition uses an EntryReference
        $definition->setConstructorInjection(MethodInjection::constructor(array(new EntryReference('foo'))));

        $container = $this->getMock('DI\Container', array(), array(), '', false);
        $container->expects($this->once())
            ->method('get')
            ->with('foo')
            ->will($this->returnValue('bar'));
        /** @var ProxyFactory $factory */
        $factory = $this->getMock('DI\Proxy\ProxyFactory', array(), array(), '', false);

        $resolver = new ClassDefinitionResolver($container, $factory);

        $object = $resolver->resolve($definition);

        $this->assertInstanceOf(self::FIXTURE_CLASS, $object);
        $this->assertEquals('bar', $object->constructorParam1);
    }

    /**
     * Check that we can inject "null" into parameters and properties
     */
    public function testResolveNullInjections()
    {
        $definition = new ClassDefinition(self::FIXTURE_CLASS);
        $definition->setConstructorInjection(MethodInjection::constructor(array(null)));
        $definition->addPropertyInjection(new PropertyInjection('prop', null));
        $resolver = $this->buildResolver();

        $object = $resolver->resolve($definition);

        $this->assertInstanceOf(self::FIXTURE_CLASS, $object);
        $this->assertNull($object->constructorParam1);
        $this->assertNull($object->prop);
    }

    public function testDefaultParameterValue()
    {
        $definition = new ClassDefinition(self::FIXTURE_CLASS);
        $definition->setConstructorInjection(MethodInjection::constructor(array('')));
        $definition->addMethodInjection(new MethodInjection('methodDefaultValue'));
        $resolver = $this->buildResolver();

        $object = $resolver->resolve($definition);

        $this->assertInstanceOf(self::FIXTURE_CLASS, $object);
        $this->assertEquals('defaultValue', $object->methodParam2);
    }

    public function testGetContainer()
    {
        /** @var \DI\Container $container */
        $container = $this->getMock('DI\Container', array(), array(), '', false);
        /** @var ProxyFactory $factory */
        $factory = $this->getMock('DI\Proxy\ProxyFactory', array(), array(), '', false);

        $resolver = new ClassDefinitionResolver($container, $factory);

        $this->assertSame($container, $resolver->getContainer());
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
        $resolver = $this->buildResolver();

        $resolver->resolve($definition);
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
        $resolver = $this->buildResolver();

        $resolver->resolve($definition);
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
        $resolver = $this->buildResolver();

        $resolver->resolve($definition);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage This definition resolver is only compatible with ClassDefinition objects, DI\Definition\FactoryDefinition given
     */
    public function testInvalidDefinitionType()
    {
        /** @var \DI\Container $container */
        $container = $this->getMock('DI\Container', array(), array(), '', false);
        /** @var ProxyFactory $factory */
        $factory = $this->getMock('DI\Proxy\ProxyFactory', array(), array(), '', false);

        $definition = new FactoryDefinition('foo', function () {
        });
        $resolver = new ClassDefinitionResolver($container, $factory);

        $resolver->resolve($definition);
    }

    public function testIsResolvable()
    {
        $resolver = $this->buildResolver();

        $classDefinition = new ClassDefinition(self::FIXTURE_CLASS);
        $this->assertTrue($resolver->isResolvable($classDefinition));

        $interfaceDefinition = new ClassDefinition(self::FIXTURE_INTERFACE);
        $this->assertFalse($resolver->isResolvable($interfaceDefinition));

        $abstractClassDefinition = new ClassDefinition(self::FIXTURE_ABSTRACT_CLASS);
        $this->assertFalse($resolver->isResolvable($abstractClassDefinition));
    }

    private function buildResolver()
    {
        /** @var \DI\Container $container */
        $container = $this->getMock('DI\Container', array(), array(), '', false);
        /** @var ProxyFactory $factory */
        $factory = $this->getMock('DI\Proxy\ProxyFactory', array(), array(), '', false);

        return new ClassDefinitionResolver($container, $factory);
    }
}
