<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI;

use Interop\Container\Exception\NotFoundException as BaseNotFoundException;

/**
 * Exception thrown when a class or a value is not found in the container
 */
class NotFoundException extends \Exception implements BaseNotFoundException
{
}
