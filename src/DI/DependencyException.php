<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI;

use Interop\Container\Exception\ContainerException;

/**
 * Exception for the Container
 */
class DependencyException extends \Exception implements ContainerException
{
}
