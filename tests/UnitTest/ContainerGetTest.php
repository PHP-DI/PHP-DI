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

    public function testGetResolvesEntryOnce()
    {
        $container = ContainerBuilder::buildDevContainer();
        $this->assertSame($container->get('stdClass'), $container->get('stdClass'));
    }

    /**
     * Tests if instantiation unlock works. We should be able to create two instances of the same class.
     */
    public function testCircularDependencies()
    {
        $container = ContainerBuilder::buildDevContainer();
        $container->get(Singleton::class);
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
     * Tests a class can be initialized with a parameter passed by reference.
     */
    public function testPassByReferenceParameter()
    {
        $container = ContainerBuilder::buildDevContainer();
        $object = $container->get(PassByReferenceDependency::class);
        $this->assertInstanceOf(PassByReferenceDependency::class, $object);
    }
}
