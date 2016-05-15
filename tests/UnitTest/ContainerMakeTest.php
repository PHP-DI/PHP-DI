<?php

namespace DI\Test\UnitTest;

use DI\ContainerBuilder;
use DI\Test\UnitTest\Fixtures\Class1CircularDependencies;
use DI\Test\UnitTest\Fixtures\PassByReferenceDependency;
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

    public function testMakeAlwaysReturnsNewInstance()
    {
        $container = ContainerBuilder::buildDevContainer();
        $this->assertNotSame($container->make('stdClass'), $container->make('stdClass'));
    }

    /**
     * Tests if instantiation unlock works. We should be able to create two instances of the same class.
     */
    public function testCircularDependencies()
    {
        $container = ContainerBuilder::buildDevContainer();
        $container->make(Singleton::class);
        $container->make(Singleton::class);
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
