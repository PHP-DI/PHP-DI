<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.io/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace UnitTests\DI;

use DI\ContainerSingleton;

/**
 * Test for ContainerSingleton
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class ContainerSingletonTest extends \PHPUnit_Framework_TestCase
{

    public function testGetInstance()
    {
        $instance = ContainerSingleton::getInstance();
        $this->assertInstanceOf('DI\Container', $instance);
        $instance2 = ContainerSingleton::getInstance();
        $this->assertSame($instance, $instance2);
    }

}
