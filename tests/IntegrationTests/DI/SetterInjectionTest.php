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
use IntegrationTests\DI\Fixtures\SetterInjectionTest\Class1;
use IntegrationTests\DI\Fixtures\SetterInjectionTest\LazyInjectionClass;

/**
 * Test class for setter injection
 */
class SetterInjectionTest extends \PHPUnit_Framework_TestCase
{

	public function setUp() {
		// Reset the singleton instance to ensure all tests are independent
		Container::reset();
		Container::addConfiguration(array(
			'aliases' => array(
				'IntegrationTests\DI\Fixtures\SetterInjectionTest\Interface1' => 'IntegrationTests\DI\Fixtures\SetterInjectionTest\Class3'
			)
		));
	}

	public function testBasicInjection() {
		/** @var $class1 Class1 */
		$class1 = Container::getInstance()->get('IntegrationTests\DI\Fixtures\SetterInjectionTest\Class1');
		$dependency = $class1->getDependency();
		$this->assertInstanceOf('IntegrationTests\DI\Fixtures\SetterInjectionTest\Class2', $dependency);
	}

	public function testInterfaceInjection() {
		/** @var $class1 Class1 */
		$class1 = Container::getInstance()->get('IntegrationTests\DI\Fixtures\SetterInjectionTest\Class1');
		$dependency = $class1->getInterface1();
		$this->assertInstanceOf('IntegrationTests\DI\Fixtures\SetterInjectionTest\Interface1', $dependency);
		$this->assertInstanceOf('IntegrationTests\DI\Fixtures\SetterInjectionTest\Class3', $dependency);
	}

	/**
	 * Injection with lazy enabled
	 */
	public function testLazyInjection1() {
		$class = new LazyInjectionClass();
		$dependency = $class->getClass2();
		$this->assertNotNull($dependency);
		$this->assertInstanceOf('DI\Proxy\Proxy', $dependency);
		// Correct proxy resolution
		$this->assertTrue($dependency->getBoolean());
	}

	public function testLazyInjection2() {
		$class = new LazyInjectionClass();
		$this->assertTrue($class->getDependencyAttribute());
	}

	/**
	 * @expectedException \DI\Annotations\AnnotationException
	 * @expectedExceptionMessage @Inject was found on IntegrationTests\DI\Fixtures\SetterInjectionTest\Buggy1::setDependency() but the parameter $dependency has no type: impossible to deduce its type
	 */
	public function testNonTypeHintedMethod() {
		Container::getInstance()->get('IntegrationTests\DI\Fixtures\SetterInjectionTest\Buggy1');
	}

	/**
	 * @expectedException \DI\Annotations\AnnotationException
	 * @expectedExceptionMessage @Inject was found on IntegrationTests\DI\Fixtures\SetterInjectionTest\Buggy2::setDependency(), the method should have exactly one parameter
	 */
	public function testNoParametersMethod() {
		Container::getInstance()->get('IntegrationTests\DI\Fixtures\SetterInjectionTest\Buggy2');
	}

	/**
	 * @expectedException \DI\NotFoundException
	 * @expectedExceptionMessage @Inject was found on IntegrationTests\DI\Fixtures\SetterInjectionTest\Buggy3::setDependency(...) but no bean or value 'nonExistentBean' was found
	 */
	public function testNamedUnknownBean() {
		Container::getInstance()->get('IntegrationTests\DI\Fixtures\SetterInjectionTest\Buggy3');
	}

}
