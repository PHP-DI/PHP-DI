<?php

namespace TestFixtures\DependencyManagerTest;

use DI\Annotations\Value;

/**
 * Fixture class
 */
class ValueInjectionClass {

	/**
	 * @Value("db.host")
	 * @var string
	 */
	private $value;

    /**
     * Inject the dependencies
     */
    public function __construct() {
        \DI\DependencyManager::getInstance()->resolveDependencies($this);
    }

	/**
	 * @return string
	 */
	public function getValue() {
		return $this->value;
	}

}
