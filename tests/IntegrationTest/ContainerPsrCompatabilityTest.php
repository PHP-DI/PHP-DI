<?php

declare(strict_types=1);

namespace DI\Test\IntegrationTest;

use DI\ContainerBuilder;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Container\ContainerExceptionInterface;
use DI\Test\IntegrationTest\Fixtures\Class1;

class ContainerPsrCompatabilityTest extends BaseContainerTest
{
    /**
     * @dataProvider provideContainer
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideContainer')]
    public function testNotFound(ContainerBuilder $builder)
    {
        $this->expectException(NotFoundExceptionInterface::class);
        $builder->build()->get('key');
    }

    /**
     * @dataProvider provideContainer
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideContainer')]
    public function testUnresolvable(ContainerBuilder $builder)
    {
        $this->expectException(ContainerExceptionInterface::class);
        $builder->build()->get(Class1::class);
    }
}
