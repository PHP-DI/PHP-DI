<?php

namespace UnitTests\DI;

use \DI\Container;


/**
 * Test class for Container
 */
class ContainerTest extends \PHPUnit_Framework_TestCase
{

	public function setUp() {
		// Reset the singleton instance to ensure all tests are independent
		Container::reset();
	}


	public function testGetInstance() {
		$instance = Container::getInstance();
		$this->assertInstanceOf('\DI\Container', $instance);
		$instance2 = Container::getInstance();
		$this->assertSame($instance, $instance2);
	}

	public function testResetInstance() {
		$instance = Container::getInstance();
		$this->assertInstanceOf('\DI\Container', $instance);
		Container::reset();
		$instance2 = Container::getInstance();
		$this->assertNotSame($instance, $instance2);
	}

	public function testConfigurationFile1() {
		// Empty configuration file
		Container::getInstance()->addConfigurationFile(dirname(__FILE__)
			. '/Fixtures/ContainerTest/di-empty.ini');
	}

}
