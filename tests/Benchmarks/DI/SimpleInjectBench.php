<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.com/PHP-DI/
 * @copyright 2012 Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace Benchmarks\DI;

require_once __DIR__ . '/../../../vendor/autoload.php';

use DI\Container;
use Benchmarks\DI\Fixtures\NamedInjection\NamedInjectionBenchClass;
use Benchmarks\DI\Fixtures\PHPDI\PHPDIBenchClass;
use Benchmarks\DI\Fixtures\PHPDILazy\PHPDILazyBenchClass;
use Benchmarks\DI\Fixtures\NewInstance\NewBenchClass;
use Benchmarks\DI\Fixtures\Singleton\SingletonBenchClass;

/**
 * Bench of the injection of a dependency
 *
 * These numbers do not represent really a lot, but it is useful to compare to when using the cache
 */
class SimpleInjectBench extends \PHPBench\BenchCase
{

	/**
	 * Run each bench X times
	 */
	protected $_iterationNumber = 40000;

	public function setUp() {
		Container::getInstance()->set("myBean", new PHPDIBenchClass());
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
