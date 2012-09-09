<?php

use \DI\DependencyManager;
use \TestFixtures\DependencyManagerTest\Class1;


/**
 * Test class for DependencyManager.
 */
class DependencyManagerTest extends \PHPUnit_Framework_TestCase
{

	public function setUp() {
		// Reset the singleton instance to ensure all tests are independent
		DependencyManager::reset();
		// Dependency injection configuration
		DependencyManager::getInstance()->addConfigurationFile(dirname(__FILE__)
			. '/TestFixtures/DependencyManagerTest/di.ini');
	}


	public function testGetInstance() {
		$instance = DependencyManager::getInstance();
		$this->assertInstanceOf('\DI\DependencyManager', $instance);
		$instance2 = DependencyManager::getInstance();
		$this->assertSame($instance, $instance2);
	}

	public function testConfigurationFile1() {
		DependencyManager::getInstance()->addConfigurationFile(dirname(__FILE__)
			. '/TestFixtures/DependencyManagerTest/di-empty.ini');
	}

	/**
	 * Injection with a class name
	 */
	public function testInjection1() {
		$class1 = new Class1();
		$dependency = $class1->getClass2();
		$this->assertNotNull($dependency);
		$this->assertInstanceOf('\TestFixtures\DependencyManagerTest\Class2', $dependency);
	}

	/**
	 * Injection with an interface name
	 */
	public function testInjection2() {
		$class1 = new Class1();
		$dependency = $class1->getInterface1();
		$this->assertNotNull($dependency);
		$this->assertInstanceOf('\TestFixtures\DependencyManagerTest\Class3', $dependency);
	}

	public function testSingletonFactory1() {
		$factory = new \DI\Factory\SingletonFactory();
		DependencyManager::getInstance()->setFactory($factory);
		$class1_1 = new Class1();
		$class2_1 = $class1_1->getClass2();
		$class1_2 = new Class1();
		$class2_2 = $class1_2->getClass2();
		$this->assertSame($class2_1, $class2_2);
	}
	public function testSingletonFactory2() {
		DependencyManager::getInstance()->addConfigurationFile(dirname(__FILE__)
			. '/TestFixtures/DependencyManagerTest/di-singletonfactory.ini');
		$class1_1 = new Class1();
		$class2_1 = $class1_1->getClass2();
		$class1_2 = new Class1();
		$class2_2 = $class1_2->getClass2();
		$this->assertSame($class2_1, $class2_2);
	}

	public function testNewFactory1() {
		$factory = new \DI\Factory\NewFactory();
		DependencyManager::getInstance()->setFactory($factory);
		$class1_1 = new Class1();
		$class2_1 = $class1_1->getClass2();
		$class1_2 = new Class1();
		$class2_2 = $class1_2->getClass2();
		$this->assertNotSame($class2_1, $class2_2);
	}
	public function testNewFactory2() {
		DependencyManager::getInstance()->addConfigurationFile(dirname(__FILE__)
			. '/TestFixtures/DependencyManagerTest/di-newfactory.ini');
		$class1_1 = new Class1();
		$class2_1 = $class1_1->getClass2();
		$class1_2 = new Class1();
		$class2_2 = $class1_2->getClass2();
		$this->assertNotSame($class2_1, $class2_2);
	}

}
