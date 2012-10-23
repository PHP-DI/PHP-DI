<?php

namespace IntegrationTests\DI;

use \DI\Container;
use \IntegrationTests\DI\Fixtures\ValueInjectionTest\ValueInjectionClass;


/**
 * Test class for value injection
 */
class ValueInjectionTest extends \PHPUnit_Framework_TestCase
{

	public function setUp() {
		// Reset the singleton instance to ensure all tests are independent
		Container::reset();
	}

	/**
	 * Value annotation
	 */
	public function testValue1() {
		Container::getInstance()->addConfigurationFile(dirname(__FILE__)
			. '/Fixtures/ValueInjectionTest/di.ini');
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

}
