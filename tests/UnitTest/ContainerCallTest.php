<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Test\UnitTest;

use DI\ContainerBuilder;

/**
 * Tests the call() method from the container.
 *
 * @covers \DI\Container
 */
class ContainerCallTest extends \PHPUnit_Framework_TestCase
{
    public function testCallNoParameters()
    {
        $container = ContainerBuilder::buildDevContainer();

        $result = $container->call(function () {
            return 'foo';
        });

        $this->assertEquals('foo', $result);
    }
}
