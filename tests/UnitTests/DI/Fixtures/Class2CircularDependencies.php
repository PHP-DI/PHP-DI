<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.com/PHP-DI/
 * @copyright 2012 Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace UnitTests\DI\Fixtures;

use Exception;
use DI\Annotations\Scope;
use DI\Annotations\Inject;

/**
 * Fixture class for testing circular dependencies
 *
 */
class Class2CircularDependencies
{
	/**
	 * @Inject
	 * @var \UnitTests\DI\Fixtures\Class1CircularDependencies
	 */
	public $class1;
}
