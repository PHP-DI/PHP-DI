<?php

declare(strict_types=1);

namespace DI\Test\IntegrationTest\Definitions;

use DI\ContainerBuilder;
use DI\Test\IntegrationTest\BaseContainerTest;
use DI\Test\IntegrationTest\Definitions\ObjectDefinition\Enum1;
use DI\Test\IntegrationTest\Definitions\ObjectDefinition\Enum2;
use stdClass;

/**
 * Test value definitions.
 */
class ValueDefinitionTest extends BaseContainerTest
{
    /**
     * @dataProvider provideContainer
     */
    public function test_value_definitions(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            'string'  => 'foo',
            'int'     => 123,
            'object'  => new stdClass(),
            'helper'  => \DI\value('foo'),
            'closure' => \DI\value(function () {
                return 'foo';
            }),
        ]);
        $container = $builder->build();

        self::assertEntryIsCompiled($container, 'string');
        $this->assertEquals('foo', $container->get('string'));

        self::assertEntryIsCompiled($container, 'int');
        $this->assertEquals(123, $container->get('int'));

        self::assertEntryIsNotCompiled($container, 'object');
        $this->assertEquals(new stdClass(), $container->get('object'));

        self::assertEntryIsCompiled($container, 'helper');
        $this->assertEquals('foo', $container->get('helper'));

        self::assertEntryIsCompiled($container, 'closure');
        $this->assertEquals('foo', call_user_func($container->get('closure')));
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_enum_value_definitions(ContainerBuilder $builder)
    {
        if (PHP_VERSION_ID < 80100) {
            $this->markTestSkipped("PHP 8.1 required for enum value definitions");
        }

        $builder->addDefinitions([
            'unit_enum'   => Enum1::Foo,
            'backed_enum' => Enum2::Bar,
        ]);
        $container = $builder->build();

        self::assertEntryIsCompiled($container, 'unit_enum');
        $this->assertEquals(Enum1::Foo, $container->get('unit_enum'));

        self::assertEntryIsCompiled($container, 'backed_enum');
        $this->assertEquals(Enum2::Bar, $container->get('backed_enum'));
    }
}
