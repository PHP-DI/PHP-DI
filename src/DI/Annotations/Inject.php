<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.com/PHP-DI/
 * @copyright 2012 Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Annotations;

/**
 * "Inject" annotation
 * @Annotation
 * @Target("PROPERTY")
 */
class Inject {

	/**
	 * Bean name
	 * @var string
	 */
	public $name;

	/**
	 * @var boolean
	 */
	public $lazy = false;

}
