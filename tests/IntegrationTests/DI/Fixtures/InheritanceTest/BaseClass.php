<?php

namespace IntegrationTests\DI\Fixtures\InheritanceTest;

use DI\Annotations\Inject;

/**
 * Fixture class
 */
abstract class BaseClass {

	/**
	 * @Inject
	 * @var \IntegrationTests\DI\Fixtures\InheritanceTest\Dependency
	 */
	protected $dependency;

    /**
     * Inject the dependencies
     */
    public function __construct() {
        \DI\Container::getInstance()->resolveDependencies($this);
    }

	/**
	 * @return Dependency
	 */
	public function getDependency() {
		return $this->dependency;
	}

}
