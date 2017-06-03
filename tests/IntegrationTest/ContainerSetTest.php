<?php

namespace DI\Test\IntegrationTest;

use DI\ContainerBuilder;

/**
 * Tests the set() method from the container.
 */
class ContainerSetTest extends BaseContainerTest
{
    /**
     * We should be able to set a null value.
     * @see https://github.com/mnapoli/PHP-DI/issues/79
     * @dataProvider provideContainer
     */
    public function testSetNullValue(ContainerBuilder $builder)
    {
        $container = $builder->build();
        $container->set('foo', null);

        $this->assertNull($container->get('foo'));
    }

    /**
     * @see https://github.com/mnapoli/PHP-DI/issues/126
     * @test
     * @dataProvider provideContainer
     */
    public function testSetGetSetGet(ContainerBuilder $builder)
    {
        $container = $builder->build();

        $container->set('foo', 'bar');
        $container->get('foo');
        $container->set('foo', 'hello');

        $this->assertSame('hello', $container->get('foo'));
    }
}
