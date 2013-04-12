<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.io/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Definition;

/**
 * Definition
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
interface Definition
{

    /**
     * Returns the name of the entry in the container
     * @return string
     */
    function getName();

    /**
     * Merge another definition into the current definition
     *
     * In case of conflicts, the latter prevails (i.e. the other definition)
     *
     * @param Definition $definition
     */
    function merge(Definition $definition);

}
