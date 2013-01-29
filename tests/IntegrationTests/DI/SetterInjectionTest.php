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

/**
 * Test class for setter injection
 */
class SetterInjectionTest extends \PHPUnit_Framework_TestCase
{

	public function setUp() {
		// Reset the singleton instance to ensure all tests are independent
		Container::reset();
	}

	public function testBasicInjection() {
		$class1 = Container::getInstance()->get('IntegrationTests\DI\Fixtures\SetterInjectionTest\Class1');
		$dependency = $class1->getDependency();
		$this->assertInstanceOf('IntegrationTests\DI\Fixtures\SetterInjectionTest\Class2', $dependency);
	}

}
