<?php

require_once __DIR__.'/../vendor/autoload.php';

require_once 'Fixtures/New/NewBenchClass.php';
require_once 'Fixtures/New/NewDependency.php';
require_once 'Fixtures/Singleton/SingletonBenchClass.php';
require_once 'Fixtures/Singleton/SingletonDependency.php';
require_once 'Fixtures/PHPDI/PHPDIBenchClass.php';
require_once 'Fixtures/PHPDI/PHPDIDependency.php';
require_once 'Fixtures/PHPDILazy/PHPDILazyBenchClass.php';
require_once 'Fixtures/PHPDILazy/PHPDILazyDependency.php';
require_once 'Fixtures/NamedInjection/NamedInjectionBenchClass.php';

/**
 * Bench of a dependency injected
 */
class SimpleInjectBench extends \PHPBench\BenchCase
{

	/**
	 * Run each bench X times
	 */
	protected $_iterationNumber = 20000;

	public function setUp() {
		\DI\Container::getInstance()->set("myBean", new PHPDIBenchClass());
	}

	public function benchNew() {
		$class = new NewBenchClass();
	}

	public function benchSingleton() {
		$class = new SingletonBenchClass();
	}

	public function benchInject() {
		$class = new PHPDIBenchClass();
		\DI\Container::getInstance()->resolveDependencies($class);
	}

	public function benchLazyInject() {
		$class = new PHPDILazyBenchClass();
		\DI\Container::getInstance()->resolveDependencies($class);
	}

	public function benchNamedInject() {
		$class = new NamedInjectionBenchClass();
		\DI\Container::getInstance()->resolveDependencies($class);
	}

}
