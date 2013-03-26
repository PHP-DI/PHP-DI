<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.com/PHP-DI/
 * @copyright 2012 Matthieu Napoli (http://mnapoli.fr/)
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

    public function setUp()
    {
        // Reset the singleton instance to ensure all tests are independent
        Container::reset();
    }

    /**
     * Value annotation
     */
    public function testValue()
    {
        $container = Container::getInstance();
        $container->getConfiguration()->addDefinitions(
            array(
                'db.host' => 'localhost'
            )
        );
        /** @var $class ValueInjectionClass */
        $class = $container->get('IntegrationTests\DI\Fixtures\ValueInjectionTest\ValueInjectionClass');
        $value = $class->getValue();
        $this->assertEquals('localhost', $value);
    }

    /**
     * @expectedException \DI\DependencyException
     * @expectedExceptionMessage Error while injecting value in IntegrationTests\DI\Fixtures\ValueInjectionTest\ValueInjectionClass::value. No bean, value or class found for 'db.host'
     */
    public function testValueException()
    {
        $container = Container::getInstance();
        /** @var $class ValueInjectionClass */
        $container->get('IntegrationTests\DI\Fixtures\ValueInjectionTest\ValueInjectionClass');
    }

}
