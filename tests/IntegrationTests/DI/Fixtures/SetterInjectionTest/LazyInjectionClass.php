<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.com/PHP-DI/
 * @copyright 2012 Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace IntegrationTests\DI\Fixtures\SetterInjectionTest;

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
     * Inject the dependencies
     */
    public function __construct() {
        \DI\Container::getInstance()->injectAll($this);
    }

	/**
	 * @return Class2
	 */
	public function getClass2() {
		return $this->class2;
	}

	/**
	 * @throws \Exception
	 * @return boolean
	 */
	public function getDependencyAttribute() {
		if ($this->class2 === null) {
			throw new \Exception('Injection of $class2 failed');
		}
		return $this->class2->getBoolean();
	}

	/**
	 * @Inject(lazy=true)
	 * @param Class2 $class2
	 */
	public function setClass2(Class2 $class2) {
		$this->class2 = $class2;
	}

}
