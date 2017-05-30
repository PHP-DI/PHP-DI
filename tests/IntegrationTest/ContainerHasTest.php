<?php

namespace DI\Test\IntegrationTest;

use DI\ContainerBuilder;
use stdClass;

/**
 * Test class for Container.
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
    public function test_has_not(ContainerBuilder $builder)
    {
        self::assertFalse($builder->build()->has('wow'));
    }

    /**
     * @test
     * @dataProvider provideContainer
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The name parameter must be of type string
     */
    public function fails_with_non_string_parameter(ContainerBuilder $builder)
    {
        $builder->build()->has(new stdClass);
    }
}
