<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.com/PHP-DI/
 * @copyright 2012 Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace IntegrationTests\DI\Fixtures\ConstructorInjectionTest;

use IntegrationTests\DI\Fixtures\ConstructorInjectionTest\Class2;

/**
 * Fixture class
 */
class Buggy1 {

	/**
	 * @var Class2
	 */
	private $dependency;

	public function __construct($dependency) {
		$this->dependency = $dependency;
	}

	/**
	 * @return Class2
	 */
	public function getDependency() {
		return $this->dependency;
	}

}
