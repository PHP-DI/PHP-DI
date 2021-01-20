<?php

declare(strict_types=1);

namespace DI\Test\IntegrationTest;

use DI\ContainerBuilder;
use function DI\create;
use function DI\get;
use function DI\value;

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

    /**
     * @test
     * @dataProvider provideContainer
     */
    public function array_entries_can_be_overridden_by_values(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            'foo' => 'bar',
        ]);
        $container = $builder->build();

        $container->set('foo', 'hello');

        $this->assertSame('hello', $container->get('foo'));
    }

    /**
     * @see https://github.com/PHP-DI/PHP-DI/issues/674
     * @test
     * @dataProvider provideContainer
     */
    public function value_definitions_are_interpreted_as_raw_values(ContainerBuilder $builder)
    {
        $container = $builder->build();

        $foo = 'foo';
        $bar = new ContainerSetTest\Dummy();
        $baz = function() {};

        $container->set('foo', value($foo));
        $container->set('bar', value($bar));
        $container->set('baz', value($baz));

        $this->assertSame($foo, $container->get('foo'));
        $this->assertSame($bar, $container->get('bar'));
        $this->assertSame($baz, $container->get('baz'));
    }

    /**
     * @test
     * @dataProvider provideContainer
     */
    public function array_entries_can_be_overridden_by_definitions(ContainerBuilder $builder)
    {
        if ($builder->isCompilationEnabled()) {
            // This behavior is not allowed on the compiled container
            return;
        }

        $builder->addDefinitions([
            'foo' => create(\stdClass::class),
        ]);
        $container = $builder->build();

        $container->set('foo', create(ContainerSetTest\Dummy::class));

        $this->assertInstanceOf(ContainerSetTest\Dummy::class, $container->get('foo'));
    }

    /**
     * @see https://github.com/PHP-DI/PHP-DI/issues/614
     * @test
     * @dataProvider provideContainer
     */
    public function interfaces_can_be_mapped_to_implementations(ContainerBuilder $builder)
    {
        if ($builder->isCompilationEnabled()) {
            // This behavior is not allowed on the compiled container
            return;
        }

        $container = $builder->build();
        $container->set(ContainerSetTest\DummyInterface::class, get(ContainerSetTest\DummyConcrete::class));

        $this->assertInstanceOf(ContainerSetTest\DummyImplementation::class, $container->get(ContainerSetTest\DummyImplementation::class));
    }
}

namespace DI\Test\IntegrationTest\ContainerSetTest;

class Dummy
{
}

interface DummyInterface
{
}

class DummyConcrete implements DummyInterface
{
}

class DummyImplementation
{
    function __construct(DummyInterface $a)
    {
    }
}
