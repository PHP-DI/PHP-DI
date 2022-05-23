<?php

declare(strict_types=1);

namespace DI\Test\IntegrationTest;

use DI\ContainerBuilder;
use stdClass;
use TypeError;

/**
 * Tests the initialized() method of the container.
 */
class ContainerInitializedTest extends BaseContainerTest
{
    /**
     * @dataProvider provideContainer
     */
    public function test_not_initialized_by_definition(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            'foo' => 'bar',
        ]);

        // Unlike has(), entries are not initialized by definitions
        self::assertFalse($builder->build()->initialized('foo'));
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_initialized_by_definition_after_get(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            'foo' => 'bar',
        ]);
        $container = $builder->build();
        $container->get('foo');

        // Only once we get the entry for the first time is it initialized
        self::assertTrue($container->initialized('foo'));
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_initialized_when_set_directly(ContainerBuilder $builder)
    {
        $container = $builder->build();
        $container->set('foo', 'bar');

        // The entry is also initialized if set directly
        self::assertTrue($container->initialized('foo'));
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_not_initialized_when_unknown(ContainerBuilder $builder)
    {
        // Entry is not initialized in a default container
        self::assertFalse($builder->build()->initialized('foo'));
    }

    /**
     * @test
     * @dataProvider provideContainer
     */
    public function fails_with_non_string_parameter(ContainerBuilder $builder)
    {
        $this->expectException(TypeError::class);
        $builder->build()->initialized(new stdClass);
    }
}
