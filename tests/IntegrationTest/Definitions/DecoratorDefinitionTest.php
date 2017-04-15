<?php

namespace DI\Test\IntegrationTest\Definitions;

use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;

/**
 * Test decorator definitions.
 *
 * @coversNothing
 */
class DecoratorDefinitionTest extends \PHPUnit_Framework_TestCase
{
    public function test_decorate_value()
    {
        $builder = new ContainerBuilder();
        $builder->addDefinitions([
            'foo' => 'bar',
        ]);
        $builder->addDefinitions([
            'foo' => \DI\decorate(function ($previous) {
                return $previous . 'baz';
            }),
        ]);
        $container = $builder->build();

        $this->assertEquals('barbaz', $container->get('foo'));
    }

    public function test_decorate_factory()
    {
        $builder = new ContainerBuilder();
        $builder->addDefinitions([
            'foo' => function () {
                return 'bar';
            },
        ]);
        $builder->addDefinitions([
            'foo' => \DI\decorate(function ($previous) {
                return $previous . 'baz';
            }),
        ]);
        $container = $builder->build();

        $this->assertEquals('barbaz', $container->get('foo'));
    }

    public function test_decorate_object()
    {
        $builder = new ContainerBuilder();
        $builder->addDefinitions([
            'foo' => \DI\create('stdClass'),
        ]);
        $builder->addDefinitions([
            'foo' => \DI\decorate(function ($previous) {
                $previous->foo = 'bar';

                return $previous;
            }),
        ]);
        $container = $builder->build();

        $object = $container->get('foo');
        $this->assertEquals('bar', $object->foo);
    }

    public function test_decorator_gets_container()
    {
        $builder = new ContainerBuilder();
        $builder->addDefinitions([
            'foo' => 'hello ',
            'bar' => 'world',
        ]);
        $builder->addDefinitions([
            'foo' => \DI\decorate(function ($previous, ContainerInterface $container) {
                return $previous . $container->get('bar');
            }),
        ]);
        $container = $builder->build();

        $this->assertEquals('hello world', $container->get('foo'));
    }

    public function test_multiple_decorators()
    {
        $builder = new ContainerBuilder();
        $builder->addDefinitions([
            'foo' => 'bar',
        ]);
        $builder->addDefinitions([
            'foo' => \DI\decorate(function ($previous) {
                return $previous . 'baz';
            }),
        ]);
        $builder->addDefinitions([
            'foo' => \DI\decorate(function ($previous) {
                return $previous . 'bam';
            }),
        ]);
        $container = $builder->build();

        $this->assertEquals('barbazbam', $container->get('foo'));
    }

    /**
     * @expectedException \DI\Definition\Exception\InvalidDefinition
     * @expectedExceptionMessage Entry "foo" decorates nothing: no previous definition with the same name was found
     */
    public function test_decorate_must_have_previous_definition()
    {
        $builder = new ContainerBuilder();
        $builder->addDefinitions([
            'foo' => \DI\decorate(function ($previous) {
                return $previous;
            }),
        ]);
        $container = $builder->build();
        $container->get('foo');
    }

    /**
     * @expectedException \DI\DependencyException
     * @expectedExceptionMessage Error while resolving foo[0]. Decorators cannot be nested in another definition
     */
    public function test_decorator_in_array()
    {
        $builder = new ContainerBuilder();
        $builder->addDefinitions([
            'foo' => [
                \DI\decorate(function ($previous) {
                    return $previous;
                }),
            ],
        ]);
        $container = $builder->build();
        $container->get('foo');
    }
}
