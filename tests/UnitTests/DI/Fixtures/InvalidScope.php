<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace UnitTests\DI\Fixtures;

use DI\Annotation\Injectable;

/**
 * Fixture class for testing Singleton scope
 *
 * @Injectable(scope="foobar")
 */
class InvalidScope
{

}
