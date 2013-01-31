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
class NamedInjectionWithTypeMappingClass {

	/**
	 * @Inject(name="nonExistentDependencyName")
	 */
	private $dependency;

    /**
     * Inject the dependencies
     */
    public function __construct() {
        \DI\Container::getInstance()->injectAll($this);
    }

	public function getDependency() {
		return $this->dependency;
	}

}
