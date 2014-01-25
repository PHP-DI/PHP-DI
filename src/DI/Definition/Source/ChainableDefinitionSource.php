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
 * This interface represents a definition source that allows chaining another source after it.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
interface ChainableDefinitionSource extends DefinitionSource
{
    /**
     * Chain another definition source after this one.
     *
     * @param DefinitionSource $source
     */
    public function chain(DefinitionSource $source);
}
