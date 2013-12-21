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

    public function __construct($param1)
    {
        $this->constructorParam1 = $param1;
    }

    public function method($param1)
    {
        $this->methodParam1 = $param1;
    }
}
