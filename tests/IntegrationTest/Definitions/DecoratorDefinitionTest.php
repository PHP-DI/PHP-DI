<?php

declare(strict_types=1);

namespace DI\Test\IntegrationTest\Definitions;

use DI\ContainerBuilder;
use DI\Test\IntegrationTest\BaseContainerTest;
use Psr\Container\ContainerInterface;
use DI\Definition\Exception\InvalidDefinition;

/**
 * Test decorator definitions.
 */
class DecoratorDefinitionTest extends BaseContainerTest
{
    /**
     * @dataProvider provideContainer
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideContainer')]
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

        self::assertEntryIsCompiled($container, 'foo');
        self::assertEquals('barbaz', $container->get('foo'));
    }

    /**
     * @dataProvider provideContainer
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideContainer')]
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
    #[\PHPUnit\Framework\Attributes\DataProvider('provideContainer')]
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
    #[\PHPUnit\Framework\Attributes\DataProvider('provideContainer')]
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
    #[\PHPUnit\Framework\Attributes\DataProvider('provideContainer')]
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

        self::assertEntryIsCompiled($container, 'foo');
        self::assertEquals('barbazbam', $container->get('foo'));
    }

    /**
     * @dataProvider provideContainer
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideContainer')]
    public function test_decorate_must_have_previous_definition(ContainerBuilder $builder)
    {
        $this->expectException(InvalidDefinition::class);
        $this->expectExceptionMessage('Entry "foo" decorates nothing: no previous definition with the same name was found');
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
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideContainer')]
    public function test_decorator_cannot_be_nested_in_another_definition(ContainerBuilder $builder)
    {
        $this->expectException(InvalidDefinition::class);
        $this->expectExceptionMessage('Definition "foo" contains an error: Decorators cannot be nested in another definition');
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
