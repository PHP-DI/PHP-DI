<?php

namespace UnitTests\DI;

use \DI\Container;
use stdClass;

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

	public function testSetGet() {
		$container = Container::getInstance();
		$dummy = new stdClass();
		$container->set('key', $dummy);
		$this->assertSame($dummy, $container->get('key'));
	}

	public function testSetClassAlias() {
		$container = Container::getInstance();
		$dummy = new stdClass();
		$container->set('key', $dummy);
		$container->setClassAlias('alias', 'key');
		$this->assertSame($dummy, $container->get('alias'));
	}

	public function testAnnotationReader() {
		$container = Container::getInstance();
		$reader = $this->getMockForAbstractClass('Doctrine\\Common\\Annotations\\Reader');
		$container->setAnnotationReader($reader);
		$this->assertSame($reader, $container->getAnnotationReader());
	}

	public function testConfigurationFile1() {
		// Empty configuration file
		Container::getInstance()->addConfigurationFile(dirname(__FILE__)
			. '/Fixtures/ContainerTest/di-empty.ini');
	}

}
