<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.com/PHP-DI/
 * @copyright 2012 Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

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
