<?php

namespace DI\Test\IntegrationTest\Definitions;

use DI\ContainerBuilder;
use DI\Test\IntegrationTest\BaseContainerTest;
use stdClass;

/**
 * Test value definitions.
 *
 * @coversNothing
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

        $this->assertEquals('foo', $container->get('string'));
        $this->assertEquals(123, $container->get('int'));
        $this->assertEquals(new \stdClass(), $container->get('object'));
        $this->assertEquals('foo', $container->get('helper'));
        $this->assertEquals('foo', call_user_func($container->get('closure')));
    }
}
