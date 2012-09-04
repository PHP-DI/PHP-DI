<?php

namespace tests\DI;

use \DI\DefaultFactory;

// Fixtures
require_once dirname(__FILE__) . '/fixtures/DefaultFactoryTest/Class1.php';


/**
 * DefaultFactory test class
 */
class DefaultFactoryTest extends \PHPUnit_Framework_TestCase {

	/**
	 * Checks that the "NEW" strategy returns new instances every time
	 */
	public function testNewStrategy() {
		$factory = new DefaultFactory();
		$factory->setDefaultStrategy(DefaultFactory::STRATEGY_NEW);
		$instance1 = $factory->getInstance('\tests\DI\fixtures\DefaultFactoryTest\Class1');
		$instance2 = $factory->getInstance('\tests\DI\fixtures\DefaultFactoryTest\Class1');
		$this->assertNotSame($instance1, $instance2);
	}

	/**
	 * Checks that the "SINGLETON" strategy returns the same instance every time
	 */
	public function testSingletonStrategy() {
		$factory = new DefaultFactory();
		$factory->setDefaultStrategy(DefaultFactory::STRATEGY_SINGLETON);
		$instance1 = $factory->getInstance('\tests\DI\fixtures\DefaultFactoryTest\Class1');
		$instance2 = $factory->getInstance('\tests\DI\fixtures\DefaultFactoryTest\Class1');
		$this->assertSame($instance1, $instance2);
	}

	/**
	 * @expectedException \DI\FactoryException
	 */
	public function testGetInstanceException() {
		$factory = new DefaultFactory();
		$factory->getInstance('UnknownClassname');
	}

}
