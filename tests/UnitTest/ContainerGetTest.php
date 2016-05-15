<?php

namespace DI\Test\UnitTest;

use DI\ContainerBuilder;
use DI\Test\UnitTest\Fixtures\Class1CircularDependencies;
use DI\Test\UnitTest\Fixtures\InvalidScope;
use DI\Test\UnitTest\Fixtures\PassByReferenceDependency;
use DI\Test\UnitTest\Fixtures\Prototype;
use DI\Test\UnitTest\Fixtures\Singleton;
use stdClass;

/**
 * Test class for Container.
 *
 * @covers \DI\Container
 */
class ContainerGetTest extends \PHPUnit_Framework_TestCase
{
    public function testSetGet()
    {
        $container = ContainerBuilder::buildDevContainer();
        $dummy = new stdClass();
        $container->set('key', $dummy);
        $this->assertSame($dummy, $container->get('key'));
    }

    /**
     * @expectedException \DI\NotFoundException
     */
    public function testGetNotFound()
    {
        $container = ContainerBuilder::buildDevContainer();
        $container->get('key');
    }

    /**
     * @coversNothing
     */
    public function testClosureIsResolved()
    {
        $closure = function () {
            return 'hello';
        };
        $container = ContainerBuilder::buildDevContainer();
        $container->set('key', $closure);
        $this->assertEquals('hello', $container->get('key'));
    }

    public function testGetWithClassName()
    {
        $container = ContainerBuilder::buildDevContainer();
        $this->assertInstanceOf('stdClass', $container->get('stdClass'));
    }

    public function testGetWithPrototypeScope()
    {
        $builder = new ContainerBuilder();
        $builder->useAnnotations(true);
        $container = $builder->build();
        // With @Injectable(scope="prototype") annotation
        $instance1 = $container->get(Prototype::class);
        $instance2 = $container->get(Prototype::class);
        $this->assertNotSame($instance1, $instance2);
    }

    public function testGetWithSingletonScope()
    {
        $container = ContainerBuilder::buildDevContainer();
        // Without @Injectable annotation => default is Singleton
        $instance1 = $container->get('stdClass');
        $instance2 = $container->get('stdClass');
        $this->assertSame($instance1, $instance2);
        // With @Injectable(scope="singleton") annotation
        $instance3 = $container->get(Singleton::class);
        $instance4 = $container->get(Singleton::class);
        $this->assertSame($instance3, $instance4);
    }

    /**
     * @expectedException \DI\Definition\Exception\DefinitionException
     * @expectedExceptionMessage Error while reading @Injectable on DI\Test\UnitTest\Fixtures\InvalidScope: Value 'foobar' is not a valid scope
     * @coversNothing
     */
    public function testGetWithInvalidScope()
    {
        $builder = new ContainerBuilder();
        $builder->useAnnotations(true);
        $container = $builder->build();
        $container->get(InvalidScope::class);
    }

    /**
     * Tests if instantiation unlock works. We should be able to create two instances of the same class.
     */
    public function testCircularDependencies()
    {
        $container = ContainerBuilder::buildDevContainer();
        $container->get(Prototype::class);
        $container->get(Prototype::class);
    }

    /**
     * @expectedException \DI\DependencyException
     * @expectedExceptionMessage Circular dependency detected while trying to resolve entry 'DI\Test\UnitTest\Fixtures\Class1CircularDependencies'
     */
    public function testCircularDependencyException()
    {
        $builder = new ContainerBuilder();
        $builder->useAnnotations(true);
        $container = $builder->build();
        $container->get(Class1CircularDependencies::class);
    }

    /**
     * @expectedException \DI\DependencyException
     * @expectedExceptionMessage Circular dependency detected while trying to resolve entry 'foo'
     */
    public function testCircularDependencyExceptionWithAlias()
    {
        $container = ContainerBuilder::buildDevContainer();
        // Alias to itself -> infinite recursive loop
        $container->set('foo', \DI\get('foo'));
        $container->get('foo');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The name parameter must be of type string
     */
    public function testNonStringParameter()
    {
        $container = ContainerBuilder::buildDevContainer();
        $container->get(new stdClass());
    }

    /**
     * Tests a class can be initialized with a parameter passed by reference.
     */
    public function testPassByReferenceParameter()
    {
        $container = ContainerBuilder::buildDevContainer();
        $object = $container->get(PassByReferenceDependency::class);
        $this->assertInstanceOf(PassByReferenceDependency::class, $object);
    }
}
