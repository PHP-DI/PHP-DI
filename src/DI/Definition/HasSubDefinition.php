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
 * A definition that has a sub-definition.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
interface HasSubDefinition extends Definition
{
    /**
     * @return string
     */
    public function getSubDefinitionName();

    /**
     * @param Definition $definition
     */
    public function setSubDefinition(Definition $definition);
}
