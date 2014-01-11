<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.io/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace UnitTests\DI\Definition\Resolver;

use DI\Definition\CallableDefinition;
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
        $definition->setConstructorInjection(new MethodInjection('__construct', array('value2')));
        $definition->addMethodInjection(new MethodInjection('method', array('value3')));
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
        $definition->setConstructorInjection(new MethodInjection('__construct', array('foo')));
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
        $definition->setConstructorInjection(new MethodInjection('__construct', array(new EntryReference('foo'))));

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

    public function testInjectOnInstance()
    {
        $definition = new ClassDefinition('UnitTests\DI\Definition\Resolver\FixtureClass');
        $definition->addPropertyInjection(new PropertyInjection('prop', 'value1'));
        $definition->addMethodInjection(new MethodInjection('method', array('value2')));
        $resolver = $this->buildResolver();

        $object = new FixtureClass('');

        $resolver->injectOnInstance($definition, $object);

        $this->assertEquals('value1', $object->prop);
        $this->assertEquals('value2', $object->methodParam1);
    }

    public function testDefaultParameterValue()
    {
        $definition = new ClassDefinition('UnitTests\DI\Definition\Resolver\FixtureClass');
        $definition->setConstructorInjection(new MethodInjection('__construct', array('')));
        $definition->addMethodInjection(new MethodInjection('methodDefaultValue'));
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

    /**
     * @expectedException \DI\DependencyException
     * @expectedExceptionMessage ArrayAccess is not instantiable
     */
    public function testNotInstantiable()
    {
        $definition = new ClassDefinition('ArrayAccess');
        $resolver = $this->buildResolver();

        $resolver->resolve($definition);
    }

    /**
     * @expectedException \DI\Definition\Exception\DefinitionException
     * @expectedExceptionMessage The parameter 'param1' of UnitTests\DI\Definition\Resolver\FixtureClass::__construct has no value defined or guessable
     */
    public function testUndefinedInjection()
    {
        $definition = new ClassDefinition('UnitTests\DI\Definition\Resolver\FixtureClass');
        $resolver = $this->buildResolver();

        $resolver->resolve($definition);
    }

    /**
     * Tests the exception thrown for internal classes: getting the default value of a parameter
     * @expectedException \DI\Definition\Exception\DefinitionException
     * @expectedExceptionMessage The parameter 'time' of DateTime::__construct has no type defined or guessable. It has a default value, but the default value can't be read through Reflection because it is a PHP internal class.
     */
    public function testInternalClassDefaultParameterValue()
    {
        $definition = new ClassDefinition('DateTime');
        $resolver = $this->buildResolver();

        $resolver->resolve($definition);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage This definition resolver is only compatible with ClassDefinition objects, DI\Definition\CallableDefinition given
     */
    public function testInvalidDefinitionType()
    {
        /** @var \DI\Container $container */
        $container = $this->getMock('DI\Container', array(), array(), '', false);
        /** @var LazyLoadingValueHolderFactory $factory */
        $factory = $this->getMock('ProxyManager\Factory\LazyLoadingValueHolderFactory', array(), array(), '', false);

        $definition = new CallableDefinition('foo', function () {
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
