<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI;

/**
 * Describes the basic interface of a container.
 *
 * Focuses only on methods allowing to use the container, not configure it or configure entries.
 *
 * @since 4.0
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
interface ContainerInterface
{
    /**
     * Returns an entry of the container by its name.
     *
     * @param string $name Name of the entry to look for.
     *
     * @throws NotFoundException No entry was found for this name.
     * @return mixed Entry. Can be anything: object, value, ...
     */
    public function get($name);

    /**
     * Tests if the container can return an entry for the given name.
     *
     * @param string $name Name of the entry to look for.
     * @return bool
     */
    public function has($name);
}
