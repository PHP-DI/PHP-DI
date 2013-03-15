<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.com/PHP-DI/
 * @copyright 2012 Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace IntegrationTests\DI\Fixtures\ConstructorInjectionTest;

use DI\Annotations\Inject;

/**
 * Fixture class
 */
class LazyInjectionClass {

	/**
	 * @var Class2
	 */
	private $class2;

	/**
	 * @Inject(lazy=true)
	 * @param Class2 $class2
	 */
	public function __construct(Class2 $class2) {
		$this->class2 = $class2;
    }

	/**
	 * @return Class2
	 */
	public function getClass2() {
		return $this->class2;
	}

	/**
	 * @return boolean
	 */
	public function getDependencyAttribute() {
		return $this->class2->getBoolean();
	}

}
