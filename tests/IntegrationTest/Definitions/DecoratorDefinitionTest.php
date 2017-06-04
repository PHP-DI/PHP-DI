<?php

namespace DI\Test\IntegrationTest\Definitions;

use DI\ContainerBuilder;
use DI\Test\IntegrationTest\BaseContainerTest;
use Psr\Container\ContainerInterface;

/**
 * Test decorator definitions.
 */
class DecoratorDefinitionTest extends BaseContainerTest
{
    /**
     * @dataProvider provideContainer
     */
    public function test_decorate_value(ContainerBuilder $builder)
    {
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

    /**
     * @dataProvider provideContainer
     */
    public function test_decorate_factory(ContainerBuilder $builder)
    {
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

    /**
     * @dataProvider provideContainer
     */
    public function test_decorate_object(ContainerBuilder $builder)
    {
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

    /**
     * @dataProvider provideContainer
     */
    public function test_decorator_gets_container(ContainerBuilder $builder)
    {
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

    /**
     * @dataProvider provideContainer
     */
    public function test_multiple_decorators(ContainerBuilder $builder)
    {
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
     * @dataProvider provideContainer
     * @expectedException \DI\Definition\Exception\InvalidDefinition
     * @expectedExceptionMessage Entry "foo" decorates nothing: no previous definition with the same name was found
     */
    public function test_decorate_must_have_previous_definition(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            'foo' => \DI\decorate(function ($previous) {
                return $previous;
            }),
        ]);
        $container = $builder->build();
        $container->get('foo');
    }

    /**
     * @dataProvider provideContainer
     * @expectedException \DI\DependencyException
     * @expectedExceptionMessageRegExp /Error while (resolving|compiling) foo\[0\]. Decorators cannot be nested in another definition/
     */
    public function test_decorator_in_array(ContainerBuilder $builder)
    {
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
