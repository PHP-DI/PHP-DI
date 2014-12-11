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
     * Merge another definition with the current definition and returns the result.
     *
     * In case of conflicts, the current definition prevails.
     *
     * @param Definition $definition
     *
     * @return Definition Merged definition
     */
    public function merge(Definition $definition);

    /**
     * Returns true if the given definition can be merged in the current one.
     *
     * @param Definition $definition
     *
     * @return bool
     */
    public function canMerge(Definition $definition);

    /**
     * @return string
     */
    public function getExtendedDefinitionName();
}
