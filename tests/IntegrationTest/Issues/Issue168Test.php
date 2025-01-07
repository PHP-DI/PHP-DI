<?php

declare(strict_types=1);

namespace DI\Test\IntegrationTest\Issues;

use DI\ContainerBuilder;
use DI\Test\IntegrationTest\BaseContainerTest;

/**
 * Test for constructor injection of parameters that are optional, and use an
 * interface (or other uninstantiable) type hint.
 *
 * @link https://github.com/mnapoli/PHP-DI/pull/168
 */
class Issue168Test extends BaseContainerTest
{
    /**
     * @dataProvider provideContainer
     */
    public function testInterfaceOptionalParameter(ContainerBuilder $builder)
    {
        require_once __DIR__ . '/Issue168/class.php';

        $container = $builder->build();
        $object = $container->get(Issue168\TestClass::class);
        $this->assertInstanceOf(Issue168\TestClass::class, $object);
    }

    /**
     * @dataProvider provideContainer
     * @requires PHP < 8.4.0
     */
    public function testInterfaceOptionalParameterForPHP83(ContainerBuilder $builder)
    {
        require_once __DIR__ . '/Issue168/class-php83.php';

        $container = $builder->build();
        $object = $container->get(Issue168\TestClass83::class);
        $this->assertInstanceOf(Issue168\TestClass83::class, $object);
    }
}
