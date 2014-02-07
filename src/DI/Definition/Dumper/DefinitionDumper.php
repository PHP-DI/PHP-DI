<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Definition\Dumper;

use DI\Definition\Definition;

/**
 * Dumps definitions to help debugging.
 *
 * @since 4.1
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
interface DefinitionDumper
{
    /**
     * Returns the given definition as string representation.
     *
     * @param Definition $definition
     *
     * @return string
     */
    public function dump(Definition $definition);
}
