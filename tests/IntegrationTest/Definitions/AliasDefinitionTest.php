<?php

namespace DI\Test\IntegrationTest\Definitions;

use DI\ContainerBuilder;

/**
 * Test alias definitions.
 *
 * @coversNothing
 */
class AliasDefinitionTest extends BaseDefinitionTest
{
    /**
     * @dataProvider provideContainer
     */
    public function test_alias_definitions(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            'foo'  => 'Hello',
            'bar'  => \DI\get('foo'),
        ]);
        $container = $builder->build();

        self::assertEquals('Hello', $container->get('bar'));
    }
}
