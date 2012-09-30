<?php

namespace DI\Tests\Fixtures\ContainerTest;

use DI\Annotations\Inject;

/**
 * Fixture class
 */
class NamedInjectionClass {

	/**
	 * @Inject(name="namedDependency")
	 */
	private $dependency;

    /**
     * Inject the dependencies
     */
    public function __construct() {
        \DI\Container::getInstance()->resolveDependencies($this);
    }

	public function getDependency() {
		return $this->dependency;
	}

}
