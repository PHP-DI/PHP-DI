<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.com/PHP-DI/
 * @copyright 2012 Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace IntegrationTests\DI;

use DI\Container;
use IntegrationTests\DI\Fixtures\InheritanceTest\SubClass;

/**
 * Test class for bean injection
 */
class InheritanceTest extends \PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        // Reset the singleton instance to ensure all tests are independent
        Container::reset();
    }

    /**
     * Injection in a base class
     */
    public function testInjectionExtends()
    {
        /** @var $object \IntegrationTests\DI\Fixtures\InheritanceTest\SubClass */
        $instance = Container::getInstance()->get('IntegrationTests\DI\Fixtures\InheritanceTest\SubClass');
        $dependency = $instance->getDependency();
        $this->assertNotNull($dependency);
        $this->assertInstanceOf('IntegrationTests\DI\Fixtures\InheritanceTest\Dependency', $dependency);
    }

}
