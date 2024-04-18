<?php

declare(strict_types=1);

namespace DI\Test\IntegrationTest\Issues;

use DI\ContainerBuilder;
use DI\Test\IntegrationTest\BaseContainerTest;
use DI\Test\IntegrationTest\Issues\Issue881\A;
use DI\Test\IntegrationTest\Issues\Issue881\B;
use DI\Test\IntegrationTest\Issues\Issue881\C;
use DI\Test\IntegrationTest\Issues\Issue881\ClassWithVariadicParameter;
use function DI\create;
use function DI\get;

final class Issue881Test extends BaseContainerTest
{
    public function testContainerWithVariadicParameters(): void
    {
        $builder = new ContainerBuilder();
        $builder->addDefinitions([
            'foo' => create(ClassWithVariadicParameter::class)
                ->constructor(
                    get(C::class),
                    get(B::class),
                    get(A::class),
                )
                ->method(
                    'method',
                    get(A::class),
                    get(B::class),
                    get(C::class),
                ),
        ]);

        $container = $builder->build();
        $instance = $container->get('foo');

        self::assertInstanceOf(ClassWithVariadicParameter::class, $instance);
        self::assertNotEmpty($instance->a);
        self::assertNotEmpty($instance->b);
        self::assertNotEmpty($instance->c);
        self::assertNotEmpty($instance->a1);
        self::assertNotEmpty($instance->b1);
        self::assertNotEmpty($instance->c1);
    }

    public function testCompiledContainerWithVariadicParameters(): void
    {
        $builder = new ContainerBuilder();
        $builder->enableCompilation(self::COMPILATION_DIR, self::generateCompiledClassName());
        $builder->addDefinitions([
            'foo' => create(ClassWithVariadicParameter::class)
                ->constructor(
                    get(C::class),
                    get(B::class),
                    get(A::class),
                )
                ->method(
                    'method',
                    get(A::class),
                    get(B::class),
                    get(C::class),
                ),
        ]);

        $container = $builder->build();
        $instance = $container->get('foo');

        self::assertInstanceOf(ClassWithVariadicParameter::class, $instance);
        self::assertNotEmpty($instance->a);
        self::assertNotEmpty($instance->b);
        self::assertNotEmpty($instance->c);
        self::assertNotEmpty($instance->a1);
        self::assertNotEmpty($instance->b1);
        self::assertNotEmpty($instance->c1);
    }
}
