<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.io/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace IntegrationTests\DI\Issues;

use DI\ContainerBuilder;

/**
 * Test for constructor injection of parameters that are optional, and use an
 * interface (or other uninstantiable) type hint.
 *
 * @link https://github.com/mnapoli/PHP-DI/pull/168
 *
 * @coversNothing
 */
class Issue168Test extends \PHPUnit_Framework_TestCase
{
    public function testInterfaceOptionalParameter()
    {
        $container = ContainerBuilder::buildDevContainer();
        $object = $container->get('IntegrationTests\DI\Issues\TestClass');
        $this->assertInstanceOf('IntegrationTests\DI\Issues\TestClass', $object);
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
