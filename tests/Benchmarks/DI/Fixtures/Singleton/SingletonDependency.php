<?php

namespace Benchmarks\DI\Fixtures\Singleton;

class SingletonDependency
{

	private static $singletonInstance = null;

	public static function getInstance() {
		if (self::$singletonInstance == null) {
			self::$singletonInstance = new self();
		}
		return self::$singletonInstance;
	}

	private function __construct() {
	}

	public function foo() {
	}

}
