<?php

namespace TestFixtures\DependencyManagerTest;

use DI\Annotations\Inject;

/**
 * Fixture class
 */
class Class1 {

	/**
	 * @Inject
	 * @var \TestFixtures\DependencyManagerTest\Class2
	 */
	private $class2;

	/**
	 * @Inject
	 * @var \TestFixtures\DependencyManagerTest\Interface1
	 */
	private $interface1;

    /**
     * Inject the dependencies
     */
    public function __construct() {
        \DI\DependencyManager::getInstance()->resolveDependencies($this);
    }

	/**
	 * @return Class2
	 */
	public function getClass2() {
		return $this->class2;
	}

	/**
	 * @return Interface1
	 */
	public function getInterface1() {
		return $this->interface1;
	}

}
