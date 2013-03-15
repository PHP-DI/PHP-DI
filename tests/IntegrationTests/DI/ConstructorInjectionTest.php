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
use IntegrationTests\DI\Fixtures\ConstructorInjectionTest\Class1;
use IntegrationTests\DI\Fixtures\ConstructorInjectionTest\LazyInjectionClass;
use IntegrationTests\DI\Fixtures\ConstructorInjectionTest\NamedInjectionWithTypeMappingClass;
use IntegrationTests\DI\Fixtures\ConstructorInjectionTest\NamedInjectionClass;
use IntegrationTests\DI\Fixtures\ConstructorInjectionTest\NamedBean;

/**
 * Test class for constructor injection
 */
class ConstructorInjectionTest extends \PHPUnit_Framework_TestCase
{

	public function setUp() {
		// Reset the singleton instance to ensure all tests are independent
		Container::reset();
		Container::addConfiguration(array(
			'aliases' => array(
				'IntegrationTests\DI\Fixtures\ConstructorInjectionTest\Interface1' => 'IntegrationTests\DI\Fixtures\ConstructorInjectionTest\Class3'
			)
		));
	}

	public function testBasicInjection() {
		/** @var $class1 Class1 */
		$class1 = Container::getInstance()->get('IntegrationTests\DI\Fixtures\ConstructorInjectionTest\Class1');
		$dependency = $class1->getDependency();
		$this->assertInstanceOf('IntegrationTests\DI\Fixtures\ConstructorInjectionTest\Class2', $dependency);
	}

	public function testInterfaceInjection() {
		/** @var $class1 Class1 */
		$class1 = Container::getInstance()->get('IntegrationTests\DI\Fixtures\ConstructorInjectionTest\Class1');
		$dependency = $class1->getInterface1();
		$this->assertInstanceOf('IntegrationTests\DI\Fixtures\ConstructorInjectionTest\Interface1', $dependency);
		$this->assertInstanceOf('IntegrationTests\DI\Fixtures\ConstructorInjectionTest\Class3', $dependency);
	}

	/**
	 * Injection with lazy enabled
	 */
	public function testLazyInjection1() {
		$this->markTestSkipped("TODO");
		$class = new LazyInjectionClass();
		$dependency = $class->getClass2();
		$this->assertNotNull($dependency);
		$this->assertInstanceOf('DI\Proxy\Proxy', $dependency);
		// Correct proxy resolution
		$this->assertTrue($dependency->getBoolean());
	}

	public function testLazyInjection2() {
		$this->markTestSkipped("TODO");
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
		$class = Container::getInstance()->get('IntegrationTests\DI\Fixtures\ConstructorInjectionTest\NamedInjectionClass');
		$dependency = $class->getDependency();
		$this->assertNotNull($dependency);
		$this->assertInstanceOf('\IntegrationTests\DI\Fixtures\ConstructorInjectionTest\NamedBean', $dependency);
		$this->assertEquals('namedDependency', $dependency->nameForTest);
		$this->assertSame($bean, $dependency);
		$this->assertNotSame($bean2, $dependency);
	}

	/**
	 * @expectedException \DI\NotFoundException
	 */
	public function testNamedInjectionNotFound() {
		// Exception (bean not defined)
		Container::getInstance()->get('IntegrationTests\DI\Fixtures\ConstructorInjectionTest\NamedInjectionClass');
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
		$class = Container::getInstance()->get('IntegrationTests\DI\Fixtures\ConstructorInjectionTest\NamedInjectionWithTypeMappingClass');
		$dependency = $class->getDependency();
		$this->assertNotNull($dependency);
		$this->assertInstanceOf('\IntegrationTests\DI\Fixtures\ConstructorInjectionTest\NamedBean', $dependency);
		$this->assertSame($bean, $dependency);
	}

	/**
	 * @expectedException \DI\Annotations\AnnotationException
	 * @expectedExceptionMessage The parameter dependency of the constructor of  has no type: impossible to deduce its type
	 */
	public function testNonTypeHintedMethod() {
		Container::getInstance()->get('IntegrationTests\DI\Fixtures\ConstructorInjectionTest\Buggy1');
	}

	public function testNamedUnknownBean() {
		Container::getInstance()->get('IntegrationTests\DI\Fixtures\ConstructorInjectionTest\Buggy2');
	}

}
