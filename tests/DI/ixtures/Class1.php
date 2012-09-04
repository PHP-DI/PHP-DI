<?php

use DI\Annotations\Inject;

class Class1 {

	/**
	 * @Inject
	 * @var Class2
	 */
	private $class2;

	/**
	 * @Inject
	 * @var Interface1
	 */
	private $interface1;

    /**
     * Inject the dependencies
     */
    public function __construct() {
        \DI\DependencyManager::getInstance()->resolveDependencies($this);
    }

	public function getClass2() {
		return $this->class2;
	}

	public function getInterface1() {
		return $this->interface1;
	}

}
