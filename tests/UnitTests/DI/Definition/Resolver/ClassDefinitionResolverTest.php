<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.io/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace UnitTests\DI\Definition\Resolver;

use DI\Definition\FactoryDefinition;
use DI\Definition\ClassDefinition;
use DI\Definition\ClassDefinition\MethodInjection;
use DI\Definition\ClassDefinition\PropertyInjection;
use DI\Definition\EntryReference;
use DI\Definition\Resolver\ClassDefinitionResolver;
use ProxyManager\Factory\LazyLoadingValueHolderFactory;

/**
 * @covers \DI\Definition\Resolver\ClassDefinitionResolver
 */
class ClassDefinitionResolverTest extends \PHPUnit_Framework_TestCase
{
    public function testResolve()
    {
        $definition = new ClassDefinition('UnitTests\DI\Definition\Resolver\FixtureClass');
        $definition->addPropertyInjection(new PropertyInjection('prop', 'value1'));
        $definition->setConstructorInjection(
            new MethodInjection('UnitTests\DI\Definition\Resolver\FixtureClass', '__construct', array('value2'))
        );
        $definition->addMethodInjection(
            new MethodInjection('UnitTests\DI\Definition\Resolver\FixtureClass', 'method', array('value3'))
        );
        $resolver = $this->buildResolver();

        $object = $resolver->resolve($definition);

        $this->assertInstanceOf('UnitTests\DI\Definition\Resolver\FixtureClass', $object);
        $this->assertEquals('value1', $object->prop);
        $this->assertEquals('value2', $object->constructorParam1);
        $this->assertEquals('value3', $object->methodParam1);
    }

    public function testResolveNoConstructorClass()
    {
        $definition = new ClassDefinition('UnitTests\DI\Definition\Resolver\NoConstructor');
        $resolver = $this->buildResolver();

        $object = $resolver->resolve($definition);
        $this->assertInstanceOf('UnitTests\DI\Definition\Resolver\NoConstructor', $object);
    }

    public function testResolveWithParameters()
    {
        $definition = new ClassDefinition('UnitTests\DI\Definition\Resolver\FixtureClass');
        $resolver = $this->buildResolver();

        $object = $resolver->resolve($definition, array('param1' => 'value'));

        $this->assertInstanceOf('UnitTests\DI\Definition\Resolver\FixtureClass', $object);
        $this->assertEquals('value', $object->constructorParam1);
    }

    /**
     * Check that given parameters override the definition
     */
    public function testResolveWithParametersAndDefinition()
    {
        $definition = new ClassDefinition('UnitTests\DI\Definition\Resolver\FixtureClass');
        $definition->setConstructorInjection(
            new MethodInjection('UnitTests\DI\Definition\Resolver\FixtureClass', '__construct', array('foo'))
        );
        $resolver = $this->buildResolver();

        $object = $resolver->resolve($definition, array('param1' => 'bar'));

        $this->assertInstanceOf('UnitTests\DI\Definition\Resolver\FixtureClass', $object);
        $this->assertEquals('bar', $object->constructorParam1);
    }

    /**
     * Check that useless parameters are ignored (no error)
     */
    public function testResolveWithUselessParameters()
    {
        $definition = new ClassDefinition('UnitTests\DI\Definition\Resolver\FixtureClass');
        $resolver = $this->buildResolver();

        $object = $resolver->resolve($definition, array('param1' => 'value', 'unknown' => 'foo'));

        $this->assertInstanceOf('UnitTests\DI\Definition\Resolver\FixtureClass', $object);
        $this->assertEquals('value', $object->constructorParam1);
    }

    /**
     * Check that entry references (in the definition) are resolved using the container
     */
    public function testResolveWithEntryReference()
    {
        $definition = new ClassDefinition('UnitTests\DI\Definition\Resolver\FixtureClass');
        // The constructor definition uses an EntryReference
        $definition->setConstructorInjection(
            new MethodInjection('UnitTests\DI\Definition\Resolver\FixtureClass', '__construct', array(new EntryReference('foo')))
        );

        $container = $this->getMock('DI\Container', array(), array(), '', false);
        $container->expects($this->once())
            ->method('get')
            ->with('foo')
            ->will($this->returnValue('bar'));
        /** @var LazyLoadingValueHolderFactory $factory */
        $factory = $this->getMock('ProxyManager\Factory\LazyLoadingValueHolderFactory', array(), array(), '', false);

        $resolver = new ClassDefinitionResolver($container, $factory);

        $object = $resolver->resolve($definition);

        $this->assertInstanceOf('UnitTests\DI\Definition\Resolver\FixtureClass', $object);
        $this->assertEquals('bar', $object->constructorParam1);
    }

    /**
     * Check that we can inject "null" into parameters and properties
     */
    public function testResolveNullInjections()
    {
        $definition = new ClassDefinition('UnitTests\DI\Definition\Resolver\FixtureClass');
        $definition->setConstructorInjection(
            new MethodInjection('UnitTests\DI\Definition\Resolver\FixtureClass', '__construct', array(null))
        );
        $definition->addPropertyInjection(new PropertyInjection('prop', null));
        $resolver = $this->buildResolver();

        $object = $resolver->resolve($definition);

        $this->assertInstanceOf('UnitTests\DI\Definition\Resolver\FixtureClass', $object);
        $this->assertNull($object->constructorParam1);
        $this->assertNull($object->prop);
    }

    public function testInjectOnInstance()
    {
        $definition = new ClassDefinition('UnitTests\DI\Definition\Resolver\FixtureClass');
        $definition->addPropertyInjection(new PropertyInjection('prop', 'value1'));
        $definition->addMethodInjection(
            new MethodInjection('UnitTests\DI\Definition\Resolver\FixtureClass', 'method', array('value2'))
        );
        $resolver = $this->buildResolver();

        $object = new FixtureClass('');

        $resolver->injectOnInstance($definition, $object);

        $this->assertEquals('value1', $object->prop);
        $this->assertEquals('value2', $object->methodParam1);
    }

    public function testDefaultParameterValue()
    {
        $definition = new ClassDefinition('UnitTests\DI\Definition\Resolver\FixtureClass');
        $definition->setConstructorInjection(
            new MethodInjection('UnitTests\DI\Definition\Resolver\FixtureClass', '__construct', array(''))
        );
        $definition->addMethodInjection(
            new MethodInjection('UnitTests\DI\Definition\Resolver\FixtureClass', 'methodDefaultValue')
        );
        $resolver = $this->buildResolver();

        $object = $resolver->resolve($definition);

        $this->assertInstanceOf('UnitTests\DI\Definition\Resolver\FixtureClass', $object);
        $this->assertEquals('defaultValue', $object->methodParam2);
    }

    public function testGetContainer()
    {
        /** @var \DI\Container $container */
        $container = $this->getMock('DI\Container', array(), array(), '', false);
        /** @var LazyLoadingValueHolderFactory $factory */
        $factory = $this->getMock('ProxyManager\Factory\LazyLoadingValueHolderFactory', array(), array(), '', false);

        $resolver = new ClassDefinitionResolver($container, $factory);

        $this->assertSame($container, $resolver->getContainer());
    }

    public function testUnknownClass()
    {
        $message = <<<MESSAGE
Entry foo cannot be resolved: the class bar doesn't exist
Definition of foo:
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
Entry ArrayAccess cannot be resolved: ArrayAccess is not instantiable
Definition of ArrayAccess:
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
Entry UnitTests\DI\Definition\Resolver\FixtureClass cannot be resolved: The parameter 'param1' of UnitTests\DI\Definition\Resolver\FixtureClass::__construct has no value defined or guessable
Definition of UnitTests\DI\Definition\Resolver\FixtureClass:
Object (
    class = UnitTests\DI\Definition\Resolver\FixtureClass
    scope = singleton
    lazy = false
)
MESSAGE;
        $this->setExpectedException('DI\Definition\Exception\DefinitionException', $message);

        $definition = new ClassDefinition('UnitTests\DI\Definition\Resolver\FixtureClass');
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
        /** @var LazyLoadingValueHolderFactory $factory */
        $factory = $this->getMock('ProxyManager\Factory\LazyLoadingValueHolderFactory', array(), array(), '', false);

        $definition = new FactoryDefinition('foo', function () {
        });
        $resolver = new ClassDefinitionResolver($container, $factory);

        $resolver->resolve($definition);
    }

    private function buildResolver()
    {
        /** @var \DI\Container $container */
        $container = $this->getMock('DI\Container', array(), array(), '', false);
        /** @var LazyLoadingValueHolderFactory $factory */
        $factory = $this->getMock('ProxyManager\Factory\LazyLoadingValueHolderFactory', array(), array(), '', false);

        return new ClassDefinitionResolver($container, $factory);
    }
}

class FixtureClass
{
    public $prop;
    public $constructorParam1;
    public $methodParam1;
    public $methodParam2;

    public function __construct($param1)
    {
        $this->constructorParam1 = $param1;
    }

    public function method($param1)
    {
        $this->methodParam1 = $param1;
    }

    public function methodDefaultValue($param = 'defaultValue')
    {
        $this->methodParam2 = $param;
    }
}

class NoConstructor
{
}
