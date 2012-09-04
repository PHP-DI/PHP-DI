<?php

use \DI\DependencyManager;
use \TestFixtures\DependencyManagerTest\Class1;


/**
 * Test class for DependencyManager.
 */
class DependencyManagerTest extends \PHPUnit_Framework_TestCase
{

	public function setUp() {
		// Dependency injection configuration
		DependencyManager::getInstance()->setConfiguration(dirname(__FILE__) . '/di.ini');
	}

	public function testGetInstance() {
		$instance = DependencyManager::getInstance();
		$this->assertInstanceOf('\DI\DependencyManager', $instance);
		$instance2 = DependencyManager::getInstance();
		$this->assertSame($instance, $instance2);
	}

	public function testResolveDependencies() {
		$class1 = new Class1();
		$dependency = $class1->getClass2();
		$this->assertNotNull($dependency);
		$this->assertInstanceOf('\TestFixtures\DependencyManagerTest\Class2', $dependency);
	}

	public function testDefaultFactorySingleton() {
		$class1_1 = new Class1();
		$class2_1 = $class1_1->getClass2();
		$class1_2 = new Class1();
		$class2_2 = $class1_2->getClass2();
		$this->assertSame($class2_1, $class2_2);
	}

}
