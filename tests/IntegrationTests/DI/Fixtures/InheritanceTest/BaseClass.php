<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.com/PHP-DI/
 * @copyright 2012 Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace IntegrationTests\DI\Fixtures\InheritanceTest;

use DI\Annotations\Inject;

/**
 * Fixture class
 */
abstract class BaseClass {

	/**
	 * @Inject
	 * @var \IntegrationTests\DI\Fixtures\InheritanceTest\Dependency
	 */
	protected $dependency;

    /**
     * Inject the dependencies
     */
    public function __construct() {
        \DI\Container::getInstance()->resolveDependencies($this);
    }

	/**
	 * @return Dependency
	 */
	public function getDependency() {
		return $this->dependency;
	}

}
