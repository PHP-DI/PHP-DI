<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.com/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI;

use Interop\Container\ContainerInterface as ContainerInteropInterface;

/**
 * Describes the basic interface of a container.
 *
 * Focuses only on methods allowing to use the container, not configure it or configure entries.
 *
 * @since 4.0
 * @deprecated Use Interop\Container\ContainerInterface instead, will be removed in 5.0.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
interface ContainerInterface extends ContainerInteropInterface
{
}
