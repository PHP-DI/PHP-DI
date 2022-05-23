<?php

declare(strict_types=1);

namespace DI\Test\IntegrationTest;

use DI\ContainerBuilder;
use stdClass;
use TypeError;

/**
 * Tests the reset() method of the container.
 */
class ContainerResetTest extends BaseContainerTest
{
    /**
     * @dataProvider provideContainer
     */
    public function test_reset_with_definition(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            'foo' => 'bar',
        ]);
        $container = $builder->build();

        // Sanity check the state of the entry prior to reset
        self::assertTrue($container->has('foo'));
        self::assertFalse($container->initialized('foo'));

        // Now initialize the entry, and sanity check the state again
        self::assertSame('bar', $container->get('foo'));
        self::assertTrue($container->has('foo'));
        self::assertTrue($container->initialized('foo'));

        // Then reset the entry
        $container->reset('foo');

        // It should still be gettable, but it is no longer initialized
        self::assertTrue($container->has('foo'));
        self::assertFalse($container->initialized('foo'));
        self::assertSame('bar', $container->get('foo'));
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_reset_when_set_directly(ContainerBuilder $builder)
    {
        $container = $builder->build();
        $container->set('foo', 'bar');

        // After resetting an entry that does not have a definition (i.e. it is
        // only set directly), it is no longer gettable.
        $container->reset('foo');
        self::assertFalse($container->has('foo'));
    }

    /**
     * @test
     * @dataProvider provideContainer
     */
    public function fails_with_non_string_parameter(ContainerBuilder $builder)
    {
        $this->expectException(TypeError::class);
        $builder->build()->reset(new stdClass);
    }
}
