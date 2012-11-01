<?php

namespace IntegrationTests\DI;

use DI\Container;
use IntegrationTests\DI\Fixtures\InheritanceTest\SubClass;

/**
 * Test class for bean injection
 */
class InheritanceTest extends \PHPUnit_Framework_TestCase
{

	public function setUp() {
		// Reset the singleton instance to ensure all tests are independent
		Container::reset();
	}


	/**
	 * Injection in a base class
	 */
	public function testInjectionExtends() {
		$instance = new SubClass();
		$dependency = $instance->getDependency();
		$this->assertNotNull($dependency);
		$this->assertInstanceOf('\IntegrationTests\DI\Fixtures\InheritanceTest\Dependency', $dependency);
	}

}
