<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.com/PHP-DI/
 * @copyright 2012 Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Definition;

/**
 * Reads DI class metadata
 */
interface DefinitionReader
{

    /**
     * Returns DI definition for the entry name
     * @param string $name
     * @return Definition|null
     */
    public function getDefinition($name);

}
