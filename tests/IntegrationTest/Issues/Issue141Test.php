<?php

declare(strict_types=1);

namespace DI\Test\IntegrationTest\Issues;

use DI\ContainerBuilder;
use DI\Test\IntegrationTest\BaseContainerTest;

/**
 * Test that chaining several sources works.
 *
 * @see https://github.com/mnapoli/PHP-DI/issues/141
 */
class Issue141Test extends BaseContainerTest
{
    /**
     * @dataProvider provideContainer
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideContainer')]
    public function testIssue141(ContainerBuilder $builder)
    {
        $builder->addDefinitions(__DIR__ . '/Issue141/config1.php');
        $builder->addDefinitions(__DIR__ . '/Issue141/config2.php');
        $container = $builder->build();

        $this->assertEquals('bar1', $container->get('foo1'));
        $this->assertEquals('bar2', $container->get('foo2'));
    }
}
