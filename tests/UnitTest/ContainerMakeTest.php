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
class ContainerMakeTest extends \PHPUnit_Framework_TestCase
{
    public function testSetMake()
    {
        $container = ContainerBuilder::buildDevContainer();
        $dummy = new stdClass();
        $container->set('key', $dummy);
        $this->assertSame($dummy, $container->make('key'));
    }

    /**
     * @expectedException \DI\NotFoundException
     */
    public function testMakeNotFound()
    {
        $container = ContainerBuilder::buildDevContainer();
        $container->make('key');
    }

    public function testMakeWithClassName()
    {
        $container = ContainerBuilder::buildDevContainer();
        $this->assertInstanceOf('stdClass', $container->make('stdClass'));
    }

    /**
     * Checks that the singleton scope is ignored.
     */
    public function testGetWithSingletonScope()
    {
        $container = ContainerBuilder::buildDevContainer();
        // Without @Injectable annotation => default is Singleton
        $instance1 = $container->make('stdClass');
        $instance2 = $container->make('stdClass');
        $this->assertNotSame($instance1, $instance2);
        // With @Injectable(scope="singleton") annotation
        $instance3 = $container->make(Singleton::class);
        $instance4 = $container->make(Singleton::class);
        $this->assertNotSame($instance3, $instance4);
    }

    public function testMakeWithPrototypeScope()
    {
        $container = ContainerBuilder::buildDevContainer();
        // With @Injectable(scope="prototype") annotation
        $instance1 = $container->make(Prototype::class);
        $instance2 = $container->make(Prototype::class);
        $this->assertNotSame($instance1, $instance2);
    }

    /**
     * @expectedException \DI\Definition\Exception\InvalidDefinition
     * @expectedExceptionMessage Error while reading @Injectable on DI\Test\UnitTest\Fixtures\InvalidScope: Value 'foobar' is not a valid scope
     * @coversNothing
     */
    public function testMakeWithInvalidScope()
    {
        $builder = new ContainerBuilder();
        $builder->useAnnotations(true);
        $container = $builder->build();
        $container->make(InvalidScope::class);
    }

    /**
     * Tests if instantiation unlock works. We should be able to create two instances of the same class.
     */
    public function testCircularDependencies()
    {
        $container = ContainerBuilder::buildDevContainer();
        $container->make(Prototype::class);
        $container->make(Prototype::class);
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
        $container->make(Class1CircularDependencies::class);
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
        $container->make('foo');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The name parameter must be of type string
     */
    public function testNonStringParameter()
    {
        $container = ContainerBuilder::buildDevContainer();
        $container->make(new stdClass());
    }

    /**
     * Tests a dependency can be made when a dependency is passed by reference.
     */
    public function testPassByReferenceParameter()
    {
        $container = ContainerBuilder::buildDevContainer();
        $container->make(PassByReferenceDependency::class);
    }

    /**
     * Tests the parameter can be provided by reference.
     */
    public function testProvidedPassByReferenceParameter()
    {
        $object = new stdClass();
        $container = ContainerBuilder::buildDevContainer();
        $container->make(PassByReferenceDependency::class, [
            'object' => &$object,
        ]);
        $this->assertEquals('bar', $object->foo);
    }
}
