<?php

declare(strict_types=1);

namespace DI\Test\IntegrationTest\Definitions;

use DI\ContainerBuilder;
use DI\Test\IntegrationTest\BaseContainerTest;

/**
 * Test alias definitions.
 */
class AliasTest extends BaseContainerTest
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
