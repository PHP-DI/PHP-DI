<?php

namespace IntegrationTests\DI\Fixtures\BeanInjectionTest;

use \DI\Annotations\Inject;
use \IntegrationTests\DI\Fixtures\BeanInjectionTest\Class2;

/**
 * Fixture class
 */
class Issue14 {

	/**
	 * @Inject
	 * @var \IntegrationTests\DI\Fixtures\BeanInjectionTest\Class2
	 */
	private $class2;

	/**
	 * @Inject
	 * @var \IntegrationTests\DI\Fixtures\BeanInjectionTest\Interface1
	 */
	private $interface1;

	/**
	 * @return Class2
	 */
	public function getClass2() {
		return $this->class2;
	}

	/**
	 * @param Class2 $class2
	 */
	public function setClass2($class2) {
		$this->class2 = $class2;
	}

}
