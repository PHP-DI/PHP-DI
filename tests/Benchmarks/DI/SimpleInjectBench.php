<?php

namespace Benchmarks\DI;

require_once __DIR__ . '/../../../vendor/autoload.php';

use Benchmarks\DI\Fixtures\NamedInjection\NamedInjectionBenchClass;
use Benchmarks\DI\Fixtures\PHPDI\PHPDIBenchClass;
use Benchmarks\DI\Fixtures\PHPDILazy\PHPDILazyBenchClass;
use Benchmarks\DI\Fixtures\NewInstance\NewBenchClass;
use Benchmarks\DI\Fixtures\Singleton\SingletonBenchClass;

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
