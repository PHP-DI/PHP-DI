<?php

namespace DI\Tests;

use \DI\Factory\NewFactory;
use \DI\Tests\Fixtures\Factory\Class1;


/**
 * NewFactory test class
 */
class NewFactoryTest extends \PHPUnit_Framework_TestCase
{

	/**
	 * Checks that the factory returns the same instance every time
	 */
	public function testGetInstance() {
		$factory = new NewFactory();
		$instance1 = $factory->getInstance('\DI\Tests\Fixtures\Factory\Class1');
		$instance2 = $factory->getInstance('\DI\Tests\Fixtures\Factory\Class1');
		$this->assertNotSame($instance1, $instance2);
		$this->assertEquals($instance1, $instance2);
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testGetInstanceException() {
		$factory = new NewFactory();
		$factory->getInstance('UnknownClassname');
	}

}
