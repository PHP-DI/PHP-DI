<?php

namespace DI\Test\UnitTest;

use DI\ContainerBuilder;
use DI\Test\IntegrationTest\BaseContainerTest;
use DI\Test\UnitTest\Fixtures\Class1CircularDependencies;
use DI\Test\UnitTest\Fixtures\InvalidScope;
use DI\Test\UnitTest\Fixtures\PassByReferenceDependency;
use DI\Test\UnitTest\Fixtures\Prototype;
use DI\Test\UnitTest\Fixtures\Singleton;
use stdClass;

/**
 * Test class for Container.
 */
class ContainerMakeTest extends BaseContainerTest
{
    /**
     * @dataProvider provideContainer
     */
    public function testSetMake(ContainerBuilder $builder)
    {
        $container = $builder->build();
        $dummy = new stdClass();
        $container->set('key', $dummy);
        $this->assertSame($dummy, $container->make('key'));
    }

    /**
     * @dataProvider provideContainer
     * @expectedException \DI\NotFoundException
     */
    public function testMakeNotFound(ContainerBuilder $builder)
    {
        $builder->build()->make('key');
    }

    /**
     * @dataProvider provideContainer
     */
    public function testMakeWithClassName(ContainerBuilder $builder)
    {
        $this->assertInstanceOf('stdClass', $builder->build()->make('stdClass'));
    }

    /**
     * Checks that the singleton scope is ignored.
     * @dataProvider provideContainer
     */
    public function testGetWithSingletonScope(ContainerBuilder $builder)
    {
        $container = $builder->build();
        // Without @Injectable annotation => default is Singleton
        $instance1 = $container->make('stdClass');
        $instance2 = $container->make('stdClass');
        $this->assertNotSame($instance1, $instance2);
        // With @Injectable(scope="singleton") annotation
        $instance3 = $container->make(Singleton::class);
        $instance4 = $container->make(Singleton::class);
        $this->assertNotSame($instance3, $instance4);
    }

    /**
     * @dataProvider provideContainer
     */
    public function testMakeWithPrototypeScope(ContainerBuilder $builder)
    {
        $container = $builder->build();
        // With @Injectable(scope="prototype") annotation
        $instance1 = $container->make(Prototype::class);
        $instance2 = $container->make(Prototype::class);
        $this->assertNotSame($instance1, $instance2);
    }

    /**
     * @dataProvider provideContainer
     * @expectedException \DI\Definition\Exception\InvalidDefinition
     * @expectedExceptionMessage Error while reading @Injectable on DI\Test\UnitTest\Fixtures\InvalidScope: Value 'foobar' is not a valid scope
     * @coversNothing
     */
    public function testMakeWithInvalidScope(ContainerBuilder $builder)
    {
        $builder->useAnnotations(true);
        $container = $builder->build();
        $container->make(InvalidScope::class);
    }

    /**
     * Tests if instantiation unlock works. We should be able to create two instances of the same class.
     * @dataProvider provideContainer
     */
    public function testCircularDependencies(ContainerBuilder $builder)
    {
        $container = $builder->build();
        $container->make(Prototype::class);
        $container->make(Prototype::class);
    }

    /**
     * @dataProvider provideContainer
     * @expectedException \DI\DependencyException
     * @expectedExceptionMessage Circular dependency detected while trying to resolve entry 'DI\Test\UnitTest\Fixtures\Class1CircularDependencies'
     */
    public function testCircularDependencyException(ContainerBuilder $builder)
    {
        $builder->useAnnotations(true);
        $container = $builder->build();
        $container->make(Class1CircularDependencies::class);
    }

    /**
     * @dataProvider provideContainer
     * @expectedException \DI\DependencyException
     * @expectedExceptionMessage Circular dependency detected while trying to resolve entry 'foo'
     */
    public function testCircularDependencyExceptionWithAlias(ContainerBuilder $builder)
    {
        $container = $builder->build();
        // Alias to itself -> infinite recursive loop
        $container->set('foo', \DI\get('foo'));
        $container->make('foo');
    }

    /**
     * @dataProvider provideContainer
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The name parameter must be of type string
     */
    public function testNonStringParameter(ContainerBuilder $builder)
    {
        $builder->build()->make(new stdClass);
    }

    /**
     * Tests a dependency can be made when a dependency is passed by reference.
     * @dataProvider provideContainer
     */
    public function testPassByReferenceParameter(ContainerBuilder $builder)
    {
        $builder->build()->make(PassByReferenceDependency::class);
    }

    /**
     * Tests the parameter can be provided by reference.
     * @dataProvider provideContainer
     */
    public function testProvidedPassByReferenceParameter(ContainerBuilder $builder)
    {
        $object = new stdClass();
        $builder->build()->make(PassByReferenceDependency::class, [
            'object' => &$object,
        ]);
        $this->assertEquals('bar', $object->foo);
    }
}
