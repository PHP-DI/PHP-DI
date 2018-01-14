<?php

declare(strict_types=1);

namespace DI\Test\IntegrationTest\Definitions;

use DI\ContainerBuilder;
use DI\Test\IntegrationTest\BaseContainerTest;
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
        $this->assertEquals(new \stdClass(), $container->get('object'));

        self::assertEntryIsCompiled($container, 'helper');
        $this->assertEquals('foo', $container->get('helper'));

        self::assertEntryIsCompiled($container, 'closure');
        $this->assertEquals('foo', call_user_func($container->get('closure')));
    }
}
