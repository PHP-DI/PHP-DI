<?php

use \DI\DefaultFactory;
use \TestFixtures\DefaultFactoryTest\Class1;


/**
 * DefaultFactory test class
 */
class DefaultFactoryTest extends \PHPUnit_Framework_TestCase
{

	/**
	 * Checks that the "NEW" strategy returns new instances every time
	 */
	public function testNewStrategy() {
		$factory = new DefaultFactory();
		$factory->setDefaultStrategy(DefaultFactory::STRATEGY_NEW);
		$instance1 = $factory->getInstance('\TestFixtures\DefaultFactoryTest\Class1');
		$instance2 = $factory->getInstance('\TestFixtures\DefaultFactoryTest\Class1');
		$this->assertNotSame($instance1, $instance2);
	}

	/**
	 * Checks that the "SINGLETON" strategy returns the same instance every time
	 */
	public function testSingletonStrategy() {
		$factory = new DefaultFactory();
		$factory->setDefaultStrategy(DefaultFactory::STRATEGY_SINGLETON);
		$instance1 = $factory->getInstance('\TestFixtures\DefaultFactoryTest\Class1');
		$instance2 = $factory->getInstance('\TestFixtures\DefaultFactoryTest\Class1');
		$this->assertSame($instance1, $instance2);
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testGetInstanceException() {
		$factory = new DefaultFactory();
		$factory->getInstance('UnknownClassname');
	}

}
