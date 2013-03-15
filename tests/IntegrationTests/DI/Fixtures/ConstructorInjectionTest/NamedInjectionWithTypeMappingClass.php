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
class NamedInjectionWithTypeMappingClass {

	private $dependency;

	/**
	 * @param mixed $dependency
	 * @Inject(param="dependency", name="nonExistentDependencyName")
	 */
	public function __construct($dependency) {
		$this->dependency = $dependency;
	}

	public function getDependency() {
		return $this->dependency;
	}

}
