<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Definition\Helper;

use DI\Definition\ArrayDefinitionExtension;

/**
 * Helps extending another definition.
 *
 * @since 5.0
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class DefinitionExtensionHelper implements DefinitionHelper
{
    /**
     * @var array
     */
    private $values = array();

    /**
     * @var string|null
     */
    private $extendedDefinitionName;

    /**
     * @param string|null $extendedEntryName Name of the entry to extend. If null, it will be the same name
     *                                       as for this definition.
     */
    public function __construct($extendedEntryName = null)
    {
        $this->extendedDefinitionName = $extendedEntryName;
    }

    /**
     * Add values to an array.
     *
     * @param array $values
     *
     * @return $this
     */
    public function add(array $values)
    {
        $this->values = $values;
        return $this;
    }

    /**
     * @param string $entryName Container entry name
     * @return ArrayDefinitionExtension
     */
    public function getDefinition($entryName)
    {
        $extendedDefinitionName = $this->extendedDefinitionName ?: $entryName;

        return new ArrayDefinitionExtension($entryName, $extendedDefinitionName, $this->values);
    }
}
