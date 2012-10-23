<?php

namespace IntegrationTests\DI\Fixtures\ValueInjectionTest;

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
        \DI\Container::getInstance()->resolveDependencies($this);
    }

	/**
	 * @return string
	 */
	public function getValue() {
		return $this->value;
	}

}
