<?php

namespace DI\Tests;

use \DI\Container;
use \DI\Tests\Fixtures\ContainerTest\Class1;
use \DI\Tests\Fixtures\ContainerTest\ValueInjectionClass;
use \DI\Tests\Fixtures\ContainerTest\LazyInjectionClass;
use \DI\Tests\Fixtures\ContainerTest\NamedBean;
use \DI\Tests\Fixtures\ContainerTest\NamedInjectionClass;


/**
 * Test class for Container.
 */
class ContainerTest extends \PHPUnit_Framework_TestCase
{

	public function setUp() {
		// Reset the singleton instance to ensure all tests are independent
		Container::reset();
		// Dependency injection configuration
		Container::getInstance()->addConfigurationFile(dirname(__FILE__)
			. '/Fixtures/ContainerTest/di.ini');
	}


	public function testGetInstance() {
		$instance = Container::getInstance();
		$this->assertInstanceOf('\DI\Container', $instance);
		$instance2 = Container::getInstance();
		$this->assertSame($instance, $instance2);
	}

	public function testConfigurationFile1() {
		Container::getInstance()->addConfigurationFile(dirname(__FILE__)
			. '/Fixtures/ContainerTest/di-empty.ini');
	}

	/**
	 * Injection with a class name
	 */
	public function testInjection1() {
		$class1 = new Class1();
		$dependency = $class1->getClass2();
		$this->assertNotNull($dependency);
		$this->assertInstanceOf('\DI\Tests\Fixtures\ContainerTest\Class2', $dependency);
	}

	/**
	 * Injection with an interface name
	 */
	public function testInjection2() {
		$class1 = new Class1();
		$dependency = $class1->getInterface1();
		$this->assertNotNull($dependency);
		$this->assertInstanceOf('\DI\Tests\Fixtures\ContainerTest\Class3', $dependency);
	}

	/**
	 * Injection with lazy enabled
	 */
	public function testLazyInjection1() {
		$class = new LazyInjectionClass();
		$dependency = $class->getClass2();
		$this->assertNotNull($dependency);
		$this->assertInstanceOf('\DI\Proxy\Proxy', $dependency);
	}
	public function testLazyInjection2() {
		$class = new LazyInjectionClass();
		$dependency = $class->getClass2();
		$this->assertNotNull($dependency);
		$this->assertTrue($dependency->getBoolean());
	}
	public function testLazyInjection3() {
		$class = new LazyInjectionClass();
		$this->assertTrue($class->getDependencyAttribute());
	}

	/**
	 * Value annotation
	 */
	public function testValue1() {
		Container::getInstance()->addConfigurationFile(dirname(__FILE__)
			. '/Fixtures/ContainerTest/di-values.ini');
		$class = new ValueInjectionClass();
		$value = $class->getValue();
		$this->assertEquals("localhost", $value);
	}
	/**
	 * @expectedException \DI\Annotations\AnnotationException
	 */
	public function testValueException() {
		$class = new ValueInjectionClass();
		$value = $class->getValue();
	}

	/**
	 * Injection of named beans
	 */
	public function testNamedInjection1() {
		// Configure the named bean
		$bean = new NamedBean();
		$bean->nameForTest = 'namedDependency';
		$container = Container::getInstance();
		$container->set('namedDependency', $bean);
		$bean2 = new NamedBean();
		$bean2->nameForTest = 'namedDependency2';
		$container = Container::getInstance();
		$container->set('namedDependency2', $bean2);
		// Test
		$class = new NamedInjectionClass();
		$dependency = $class->getDependency();
		$this->assertNotNull($dependency);
		$this->assertInstanceOf('\DI\Tests\Fixtures\ContainerTest\NamedBean', $dependency);
		$this->assertEquals('namedDependency', $dependency->nameForTest);
		$this->assertSame($bean, $dependency);
		$this->assertNotSame($bean2, $dependency);
	}
	/**
	 * @expectedException \DI\BeanNotFoundException
	 */
	public function testNamedInjection2() {
		// Exception (bean not defined)
		new NamedInjectionClass();
	}

	public function testSingleton() {
		$class1_1 = new Class1();
		$class2_1 = $class1_1->getClass2();
		$class1_2 = new Class1();
		$class2_2 = $class1_2->getClass2();
		$this->assertNotNull($class2_1);
		$this->assertSame($class2_1, $class2_2);
	}

}
