<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.com/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI;

use Mycsense\Enum\Enum;

/**
 * Scope enum
 */
class Scope extends Enum
{

	const SINGLETON = 'singleton';
	const PROTOTYPE = 'prototype';

	/**
	 * @return Scope
	 */
	public static function SINGLETON() {
		return new static(self::SINGLETON);
	}

	/**
	 * @return Scope
	 */
	public static function PROTOTYPE() {
		return new static(self::PROTOTYPE);
	}

}
