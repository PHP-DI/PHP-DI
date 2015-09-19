<?php

namespace DI\Test\IntegrationTest\Issues;

use DI\ContainerBuilder;

/**
 * Test that chaining several sources works
 *
 * @see https://github.com/mnapoli/PHP-DI/issues/141
 *
 * @coversNothing
 */
class Issue141Test extends \PHPUnit_Framework_TestCase
{
    public function testIssue141()
    {
        $builder = new ContainerBuilder();
        $builder->addDefinitions(__DIR__ . '/Issue141/config1.php');
        $builder->addDefinitions(__DIR__ . '/Issue141/config2.php');
        $container = $builder->build();

        $this->assertEquals('bar1', $container->get('foo1'));
        $this->assertEquals('bar2', $container->get('foo2'));
    }
}
