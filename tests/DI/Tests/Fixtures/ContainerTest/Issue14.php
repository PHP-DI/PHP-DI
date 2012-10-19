<?php

namespace DI\Tests\Fixtures\ContainerTest;

use DI\Annotations\Inject;
use \DI\Tests\Fixtures\ContainerTest\Class2;

/**
 * Fixture class
 */
class Issue14 {

	/**
	 * @Inject
	 * @var \DI\Tests\Fixtures\ContainerTest\Class2
	 */
	private $class2;

	/**
	 * @Inject
	 * @var \DI\Tests\Fixtures\ContainerTest\Interface1
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
