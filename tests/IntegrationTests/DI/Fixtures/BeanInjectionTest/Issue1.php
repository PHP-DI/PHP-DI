<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.com/PHP-DI/
 * @copyright 2012 Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace IntegrationTests\DI\Fixtures\BeanInjectionTest;

use \DI\Annotations\Inject;
use \IntegrationTests\DI\Fixtures\BeanInjectionTest\Class2;
use \IntegrationTests\DI\Fixtures\BeanInjectionTest\Class2 as Alias;
use \IntegrationTests\DI\Fixtures\BeanInjectionTest as NamespaceAlias;

/**
 * Fixture class
 */
class Issue1 {

	/**
	 * @Inject
	 * @var Class2
	 */
	public $class2;

	/**
	 * @Inject
	 * @var Alias
	 */
	public $alias;

	/**
	 * @Inject
	 * @var NamespaceAlias\Class2
	 */
	public $namespaceAlias;

}
