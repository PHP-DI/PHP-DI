<?php

namespace DI\Tests\Fixtures\ContainerTest;

use DI\Annotations\Inject;

/**
 * Fixture class
 */
class LazyInjectionClass {

	/**
	 * @Inject(lazy=true)
	 * @var \DI\Tests\Fixtures\ContainerTest\Class2
	 */
	private $class2;

    /**
     * Inject the dependencies
     */
    public function __construct() {
        \DI\Container::getInstance()->resolveDependencies($this);
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
