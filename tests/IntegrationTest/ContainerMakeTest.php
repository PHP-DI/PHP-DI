<?php

declare(strict_types=1);

namespace DI\Test\UnitTest;

use DI\ContainerBuilder;
use DI\Test\IntegrationTest\BaseContainerTest;
use DI\Test\UnitTest\Fixtures\Class1CircularDependencies;
use DI\Test\UnitTest\Fixtures\PassByReferenceDependency;
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
     * @dataProvider provideContainer
     */
    public function testMakeAlwaysReturnsNewInstance(ContainerBuilder $builder)
    {
        $container = $builder->build();
        $this->assertNotSame($container->make('stdClass'), $container->make('stdClass'));
    }

    /**
     * Tests if instantiation unlock works. We should be able to create two instances of the same class.
     * @dataProvider provideContainer
     */
    public function testCircularDependencies(ContainerBuilder $builder)
    {
        $container = $builder->build();
        $container->make(Singleton::class);
        $container->make(Singleton::class);
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
        $builder->addDefinitions([
            // Alias to itself -> infinite recursive loop
            'foo' => \DI\get('foo'),
        ]);
        $container = $builder->build();
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
