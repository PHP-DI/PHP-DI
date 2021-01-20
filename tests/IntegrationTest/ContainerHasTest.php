<?php

declare(strict_types=1);

namespace DI\Test\IntegrationTest;

use DI\ContainerBuilder;
use stdClass;

/**
 * Tests the has() method from the container.
 */
class ContainerHasTest extends BaseContainerTest
{
    /**
     * @dataProvider provideContainer
     */
    public function test_has(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            'foo' => 'bar',
        ]);

        self::assertTrue($builder->build()->has('foo'));
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_has_when_set_directly(ContainerBuilder $builder)
    {
        $container = $builder->build();
        $container->set('foo', 'bar');

        self::assertTrue($container->has('foo'));
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_has_not(ContainerBuilder $builder)
    {
        self::assertFalse($builder->build()->has('wow'));
    }

    /**
     * @test
     * @dataProvider provideContainer
     */
    public function fails_with_non_string_parameter(ContainerBuilder $builder)
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('The name parameter must be of type string');
        $builder->build()->has(new stdClass);
    }
}
