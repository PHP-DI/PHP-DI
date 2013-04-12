<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.io/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace IntegrationTests\DI;

use \DI\Container;
use \IntegrationTests\DI\Fixtures\ValueInjectionTest\ValueInjectionClass;

/**
 * Test class for value injection
 */
class ValueInjectionTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @expectedException \DI\DependencyException
     * @expectedExceptionMessage Error while injecting value in IntegrationTests\DI\Fixtures\ValueInjectionTest\ValueInjectionClass::value. No bean, value or class found for 'db.host'
     */
    public function testValueException()
    {
        $container = new Container();
        /** @var $class ValueInjectionClass */
        $container->get('IntegrationTests\DI\Fixtures\ValueInjectionTest\ValueInjectionClass');
    }

}
