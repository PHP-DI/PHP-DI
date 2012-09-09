<?php

use \DI\Factory\SingletonFactory;
use \TestFixtures\Factory\Class1;


/**
 * SingletonFactory test class
 */
class SingletonFactoryTest extends \PHPUnit_Framework_TestCase
{

	/**
	 * Checks that the factory returns the same instance every time
	 */
	public function testGetInstance() {
		$factory = new SingletonFactory();
		$instance1 = $factory->getInstance('\TestFixtures\Factory\Class1');
		$instance2 = $factory->getInstance('\TestFixtures\Factory\Class1');
		$this->assertSame($instance1, $instance2);
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testGetInstanceException() {
		$factory = new SingletonFactory();
		$factory->getInstance('UnknownClassname');
	}

}
