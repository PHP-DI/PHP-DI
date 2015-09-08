<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Definition\Source;

/**
 * Source that can list all the definitions it can provide.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
interface ExplorableDefinitionSource
{
    /**
     * Returns the name of the definitions the source can provide.
     *
     * @param string $name
     * @return string[]
     */
    public function getAllDefinitionNames();
}
