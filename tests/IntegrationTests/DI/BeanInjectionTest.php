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
use \IntegrationTests\DI\Fixtures\BeanInjectionTest\Class1;
use \IntegrationTests\DI\Fixtures\BeanInjectionTest\Class2;
use \IntegrationTests\DI\Fixtures\BeanInjectionTest\Issue14;
use \IntegrationTests\DI\Fixtures\BeanInjectionTest\LazyInjectionClass;
use \IntegrationTests\DI\Fixtures\BeanInjectionTest\NamedBean;
use \IntegrationTests\DI\Fixtures\BeanInjectionTest\NamedInjectionClass;
use \IntegrationTests\DI\Fixtures\BeanInjectionTest\NamedInjectionWithTypeMappingClass;

/**
 * Test class for bean injection
 */
class BeanInjectionTest extends \PHPUnit_Framework_TestCase
{

	public function setUp() {
		// Reset the singleton instance to ensure all tests are independent
		Container::reset();
		Container::addConfiguration(array(
			'aliases' => array(
				'\IntegrationTests\DI\Fixtures\BeanInjectionTest\Interface1' => '\IntegrationTests\DI\Fixtures\BeanInjectionTest\Class3'
			)
		));
	}


	public function testBasicInjection() {
		$class1 = new Class1();
		$dependency = $class1->getClass2();
		$this->assertNotNull($dependency);
		$this->assertInstanceOf('\IntegrationTests\DI\Fixtures\BeanInjectionTest\Class2', $dependency);
	}

	public function testInterfaceInjection() {
		$class1 = new Class1();
		$dependency = $class1->getInterface1();
		$this->assertNotNull($dependency);
		$this->assertInstanceOf('\IntegrationTests\DI\Fixtures\BeanInjectionTest\Class3', $dependency);
	}

	/**
	 * Injection with lazy enabled
	 */
	public function testLazyInjection1() {
		$class = new LazyInjectionClass();
		$dependency = $class->getClass2();
		$this->assertNotNull($dependency);
		$this->assertInstanceOf('\DI\Proxy\Proxy', $dependency);
		// Correct proxy resolution
		$this->assertTrue($dependency->getBoolean());
	}

	public function testLazyInjection2() {
		$class = new LazyInjectionClass();
		$this->assertTrue($class->getDependencyAttribute());
	}

	/**
	 * Injection of named beans
	 */
	public function testNamedInjection() {
		$container = Container::getInstance();
		// Configure the named bean
		$bean = new NamedBean();
		$bean->nameForTest = 'namedDependency';
		$container->set('namedDependency', $bean);
		$bean2 = new NamedBean();
		$bean2->nameForTest = 'namedDependency2';
		$container->set('namedDependency2', $bean2);
		// Test
		$class = new NamedInjectionClass();
		$dependency = $class->getDependency();
		$this->assertNotNull($dependency);
		$this->assertInstanceOf('\IntegrationTests\DI\Fixtures\BeanInjectionTest\NamedBean', $dependency);
		$this->assertEquals('namedDependency', $dependency->nameForTest);
		$this->assertSame($bean, $dependency);
		$this->assertNotSame($bean2, $dependency);
	}

	/**
	 * @expectedException \DI\NotFoundException
	 */
	public function testNamedInjectionNotFound() {
		// Exception (bean not defined)
		new NamedInjectionClass();
	}

	/**
	 * Test that type mapping also works with named injections
	 */
	public function testNamedInjectionWithTypeMapping() {
		$container = Container::getInstance();
		Container::addConfiguration(array(
			'aliases' => array(
				'nonExistentDependencyName' => 'namedDependency'
			)
		));
		// Configure the named bean
		$bean = new NamedBean();
		$container->set('namedDependency', $bean);
		// Test
		$class = new NamedInjectionWithTypeMappingClass();
		$dependency = $class->getDependency();
		$this->assertNotNull($dependency);
		$this->assertInstanceOf('\IntegrationTests\DI\Fixtures\BeanInjectionTest\NamedBean', $dependency);
		$this->assertSame($bean, $dependency);
	}

	public function testFactoryCreatesSingletons() {
		$class1_1 = new Class1();
		$class2_1 = $class1_1->getClass2();
		$class1_2 = new Class1();
		$class2_2 = $class1_2->getClass2();
		$this->assertNotNull($class2_1);
		$this->assertSame($class2_1, $class2_2);
	}

	/**
	 * Check that if a dependency is already set, the container
	 * will not overwrite it
	 */
	public function testIssue14() {
		$object = new \IntegrationTests\DI\Fixtures\BeanInjectionTest\Issue14();
		$class2 = new Class2();
		$object->setClass2($class2);
		Container::getInstance()->injectAll($object);
		$this->assertSame($class2, $object->getClass2());
	}

}
