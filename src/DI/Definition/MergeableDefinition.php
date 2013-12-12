<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Definition;

/**
 * Definition that is mergeable with another.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
interface MergeableDefinition extends Definition
{
    /**
     * Merge another definition into the current definition.
     *
     * In case of conflicts, the current definition prevails.
     *
     * @param MergeableDefinition $definition
     */
    public function merge(MergeableDefinition $definition);
}
