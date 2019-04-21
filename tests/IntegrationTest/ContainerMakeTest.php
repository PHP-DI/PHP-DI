<?php

declare(strict_types=1);

namespace DI\Test\IntegrationTest;

use DI\ContainerBuilder;
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

    /**
     * Test that PHP-7 non-exceptions are correctly handled when resolving definitions.
     * @dataProvider provideContainer
     */
    public function testThrowableDuringResolve(ContainerBuilder $builder)
    {
      $builder->addDefinitions([
        'tomorrow' => \DI\factory(function() {
          // Cause a TypeError to be thrown in PHP 7 when this gets resolved
          return (new \DateTime())->add('tomorrow');
        })
      ]);
      $container = $builder->build();
      $exception = null;
      try {
        // First resolve should throw the TypeError
        $container->make('tomorrow');
      } catch (\Throwable $e) {
        $exception = $e;
      }
      $this->assertInstanceOf('TypeError', $exception);
      $exception = null;
      try {
        // Second error must ALSO throw the TypeError, not a circular exception
        $container->make('tomorrow');
      } catch (\Throwable $e) {
        $exception = $e;
      }
      $this->assertInstanceOf('TypeError', $exception);
    }

    /**
     * @see https://github.com/PHP-DI/PHP-DI/issues/554
     * @dataProvider provideContainer
     */
    public function testMakeWithDecorator(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            Fixture\Foo::class => \DI\decorate(function ($previous) {
                return $previous;
            }),
        ]);
        $container = $builder->build();
        $result = $container->make(Fixture\Foo::class, [
            'bar' => 'baz',
        ]);
        $this->assertEquals('baz', $result->bar);
    }

    /**
     * Test that factory method can access to the values provided to the make call
     * @dataProvider provideContainer
     */
    public function testFactoryFunctionForwardsPassedParameters(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            'some_alias' => function ($bar) {
                return new Fixture\Foo($bar . ' + local_manipulation');
            },
        ]);
        $container = $builder->build();

        $result = $container->make('some_alias', [
            'bar' => 'baz'
        ]);
        $this->assertEquals('baz + local_manipulation', $result->bar);
    }
}

namespace DI\Test\IntegrationTest\Fixture;

class Foo
{
    public $bar;

    public function __construct($bar = null)
    {
        $this->bar = $bar;
    }
}
