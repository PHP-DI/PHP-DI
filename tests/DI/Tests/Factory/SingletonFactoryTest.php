<?php

namespace DI\Tests;

use \DI\Factory\SingletonFactory;
use \DI\Tests\Fixtures\Factory\Class1;


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
		$instance1 = $factory->getInstance('\DI\Tests\Fixtures\Factory\Class1');
		$instance2 = $factory->getInstance('\DI\Tests\Fixtures\Factory\Class1');
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
