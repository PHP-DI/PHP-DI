<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.com/PHP-DI/
 * @copyright 2012 Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace UnitTests\DI\Fixtures;

/**
 * Class with a constructor that has mandatory parameters
 */
class ConstructorWithParameters
{

	/**
	 * The constructor has mandatory parameters
	 */
	public function __construct($a, $b) {
	}

}
