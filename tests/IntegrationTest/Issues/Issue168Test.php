<?php

namespace DI\Test\IntegrationTest\Issues;

use DI\ContainerBuilder;

/**
 * Test for constructor injection of parameters that are optional, and use an
 * interface (or other uninstantiable) type hint.
 *
 * @link https://github.com/mnapoli/PHP-DI/pull/168
 */
class Issue168Test extends \PHPUnit_Framework_TestCase
{
    public function testInterfaceOptionalParameter()
    {
        $container = ContainerBuilder::buildDevContainer();
        $object = $container->get(TestClass::class);
        $this->assertInstanceOf(TestClass::class, $object);
    }
}

class TestClass
{
    /**
     * The parameter is optional. TestInterface is not instantiable, so `null` should
     * be injected instead of getting an exception.
     */
    public function __construct(TestInterface $param = null)
    {
    }
}

interface TestInterface
{
}
