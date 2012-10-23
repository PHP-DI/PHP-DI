<?php

namespace IntegrationTests\DI\Fixtures\BeanInjectionTest;

use DI\Annotations\Inject;

/**
 * Fixture class
 */
class Class1 {

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
	 * @return Interface1
	 */
	public function getInterface1() {
		return $this->interface1;
	}

}
