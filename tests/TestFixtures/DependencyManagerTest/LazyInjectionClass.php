<?php

namespace TestFixtures\DependencyManagerTest;

use DI\Annotations\Inject;

/**
 * Fixture class
 */
class LazyInjectionClass {

	/**
	 * @Inject(lazy=true)
	 * @var \TestFixtures\DependencyManagerTest\Class2
	 */
	private $class2;

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
	 * @return boolean
	 */
	public function getDependencyAttribute() {
		return $this->class2->getBoolean();
	}

}
